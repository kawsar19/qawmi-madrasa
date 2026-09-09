<?php

declare(strict_types=1);

use App\Http\Middleware\SetCurrentAcademicSession;
use App\Livewire\Tenant\Finance\FeeStructureList;
use App\Livewire\Tenant\Finance\InvoiceRun;
use App\Livewire\Tenant\Finance\PaymentCollect;
use App\Models\Academic\AcademicSession;
use App\Models\Academic\Jamaat;
use App\Models\Academic\Marhala;
use App\Models\Central\Tenant;
use App\Models\Finance\FeeHead;
use App\Models\Finance\FeeStructure;
use App\Models\Finance\Invoice;
use App\Models\Finance\Payment;
use App\Models\Finance\StudentDiscount;
use App\Models\Finance\StudentFeeOverride;
use App\Models\People\Enrollment;
use App\Models\People\Student;
use App\Models\User;
use App\Services\Academic\CurrentSession;
use App\Services\Finance\FeeResolver;
use App\Services\Finance\InvoiceGenerator;
use App\Services\Finance\PaymentCollector;
use App\Services\Finance\ReceiptPdf;
use App\Services\Tenancy\TenantProvisioner;
use Illuminate\Support\Str;
use Livewire\Livewire;

afterEach(fn () => tenancy()->end());

/** Pest helpers are file-scoped, so the other test files' copies cannot be reused. */
function feeTenant(string $slug = 'darul-ulum'): Tenant
{
    $tenant = app(TenantProvisioner::class)->provision(
        ['name' => 'দারুল উলুম মাদরাসা', 'slug' => $slug, 'madrasa_type' => 'kitab'],
        "{$slug}.localhost",
        [
            'name' => 'মুহতামিম সাহেব',
            'email' => "admin@{$slug}.test",
            'password' => 'password',
            'mobile' => '01712345678',
        ],
    );

    $tenant->update(['trial_ends_at' => now()->addMonth()]);

    return $tenant;
}

function feeAdmin(Tenant $tenant): User
{
    tenancy()->initialize($tenant);
    $user = User::query()->where('tenant_id', $tenant->getKey())->firstOrFail();
    tenancy()->end();

    return $user;
}

/**
 * চলতি বর্ষ, একটি ক্লাস, মাসিক বেতনের রেট — বিলের ন্যূনতম শর্ত।
 *
 * @return array{session: AcademicSession, jamaat: Jamaat, feeHead: FeeHead}
 */
function feeSetup(Tenant $tenant, float $monthlyRate = 800): array
{
    tenancy()->initialize($tenant);

    $session = AcademicSession::create(['name' => '১৪৪৬-১৪৪৭ হিজরি', 'is_current' => true]);
    $marhala = Marhala::where('code', 'hifz')->firstOrFail();
    $jamaat = Jamaat::create(['marhala_id' => $marhala->id, 'name' => '১০ পারা']);

    // সিড করা খাত — প্রতি টেন্যান্টে আগেই বসানো থাকে।
    $feeHead = FeeHead::where('code', 'monthly_fee')->firstOrFail();

    FeeStructure::create([
        'academic_session_id' => $session->id,
        'jamaat_id' => $jamaat->id,
        'fee_head_id' => $feeHead->id,
        'amount' => $monthlyRate,
    ]);

    return ['session' => $session, 'jamaat' => $jamaat, 'feeHead' => $feeHead];
}

/** ছাত্র + চলতি বর্ষের এনরোলমেন্ট। */
function feeStudent(array $setup, string $name = 'আব্দুল্লাহ', array $overrides = []): Student
{
    $student = Student::create([
        'student_uid' => 'S-'.Str::random(6),
        'name' => $name,
        'father_name' => 'আব্দুর রহমান',
        'residency_type' => Student::RESIDENCY_RESIDENTIAL,
        'status' => Student::STATUS_ACTIVE,
        ...$overrides,
    ]);

    Enrollment::create([
        'student_id' => $student->id,
        'academic_session_id' => $setup['session']->id,
        'jamaat_id' => $setup['jamaat']->id,
        'status' => Enrollment::STATUS_STUDYING,
    ]);

    return $student;
}

// ---- রেট সমাধান ----

it('resolves the class rate for a student', function () {
    $setup = feeSetup(feeTenant());
    $student = feeStudent($setup);

    $resolved = app(FeeResolver::class)->resolve(
        $student,
        $setup['feeHead'],
        $setup['session']->id,
        $setup['jamaat']->id,
    );

    expect($resolved['rate'])->toBe(800.0)
        ->and($resolved['amount'])->toBe(800.0);
});

it('lets a student override beat the class rate', function () {
    $setup = feeSetup(feeTenant());
    $student = feeStudent($setup);

    StudentFeeOverride::create([
        'student_id' => $student->id,
        'academic_session_id' => $setup['session']->id,
        'fee_head_id' => $setup['feeHead']->id,
        'amount' => 500,
    ]);

    $resolved = app(FeeResolver::class)->resolve(
        $student,
        $setup['feeHead'],
        $setup['session']->id,
        $setup['jamaat']->id,
    );

    expect($resolved['amount'])->toBe(500.0);
});

it('applies each discount kind correctly', function (string $type, float $value, float $expected) {
    $setup = feeSetup(feeTenant());
    $student = feeStudent($setup);

    StudentDiscount::create([
        'student_id' => $student->id,
        'academic_session_id' => $setup['session']->id,
        'type' => $type,
        'value' => $value,
    ]);

    $resolved = app(FeeResolver::class)->resolve(
        $student,
        $setup['feeHead'],
        $setup['session']->id,
        $setup['jamaat']->id,
    );

    expect($resolved['amount'])->toBe($expected);
})->with([
    'শতকরা ৫০%' => [StudentDiscount::TYPE_PERCENT, 50.0, 400.0],
    'নির্দিষ্ট ৩০০' => [StudentDiscount::TYPE_FIXED, 300.0, 500.0],
    'পূর্ণ মওকুফ' => [StudentDiscount::TYPE_FULL, 0.0, 0.0],
]);

it('never lets a discount push the amount below zero', function () {
    $setup = feeSetup(feeTenant());
    $student = feeStudent($setup);

    // রেটের চেয়ে বড় ছাড় — মাদরাসা ছাত্রকে টাকা দেবে না।
    StudentDiscount::create([
        'student_id' => $student->id,
        'academic_session_id' => $setup['session']->id,
        'type' => StudentDiscount::TYPE_FIXED,
        'value' => 5000,
    ]);

    $resolved = app(FeeResolver::class)->resolve(
        $student,
        $setup['feeHead'],
        $setup['session']->id,
        $setup['jamaat']->id,
    );

    expect($resolved['amount'])->toBe(0.0);
});

// ---- বিল রান ----

it('generates a monthly invoice for every studying student', function () {
    $setup = feeSetup(feeTenant());
    feeStudent($setup, 'আব্দুল্লাহ');
    feeStudent($setup, 'ইব্রাহিম');

    $result = app(InvoiceGenerator::class)->run($setup['session'], '2026-01');

    expect($result['created'])->toBe(2)
        ->and(Invoice::count())->toBe(2)
        ->and(Invoice::first()->net_amount)->toBe('800.00');
});

it('does not double-bill when the run is repeated', function () {
    $setup = feeSetup(feeTenant());
    feeStudent($setup);

    $generator = app(InvoiceGenerator::class);
    $generator->run($setup['session'], '2026-01');
    $second = $generator->run($setup['session'], '2026-01');

    // দ্বিতীয়বার কিছুই তৈরি হয়নি — unique index আটকেছে।
    expect($second['created'])->toBe(0)
        ->and($second['skipped'])->toBe(1)
        ->and(Invoice::count())->toBe(1);
});

it('skips students who have left', function () {
    $setup = feeSetup(feeTenant());
    $student = feeStudent($setup);

    Enrollment::where('student_id', $student->id)
        ->update(['status' => Enrollment::STATUS_LEFT]);

    $result = app(InvoiceGenerator::class)->run($setup['session'], '2026-01');

    expect($result['created'])->toBe(0)
        ->and(Invoice::count())->toBe(0);
});

it('marks a fully waived invoice as paid', function () {
    $setup = feeSetup(feeTenant());
    $student = feeStudent($setup);

    StudentDiscount::create([
        'student_id' => $student->id,
        'academic_session_id' => $setup['session']->id,
        'type' => StudentDiscount::TYPE_FULL,
        'value' => 0,
    ]);

    app(InvoiceGenerator::class)->run($setup['session'], '2026-01');

    $invoice = Invoice::firstOrFail();

    // রেকর্ড থাকে, কিন্তু বকেয়া নেই।
    expect($invoice->net_amount)->toBe('0.00')
        ->and($invoice->status)->toBe(Invoice::STATUS_PAID)
        ->and($invoice->dueAmount())->toBe(0.0);
});

// ---- আদায় ----

it('settles three months with a single receipt', function () {
    $setup = feeSetup(feeTenant());
    $student = feeStudent($setup);

    $generator = app(InvoiceGenerator::class);

    foreach (['2026-01', '2026-02', '2026-03'] as $month) {
        $generator->run($setup['session'], $month);
    }

    $payment = app(PaymentCollector::class)
        ->collect($student, $setup['session']->id, 2400);

    // একটাই রসিদ, তিন ইনভয়েসে বণ্টিত।
    expect($payment->receipt_no)->not->toBe('')
        ->and($payment->allocations()->count())->toBe(3)
        ->and(Invoice::where('status', Invoice::STATUS_PAID)->count())->toBe(3)
        ->and($payment->unallocatedAmount())->toBe(0.0);
});

it('records a partial payment and leaves the rest due', function () {
    $setup = feeSetup(feeTenant());
    $student = feeStudent($setup);

    app(InvoiceGenerator::class)->run($setup['session'], '2026-01');

    app(PaymentCollector::class)->collect($student, $setup['session']->id, 500);

    $invoice = Invoice::firstOrFail();

    expect($invoice->status)->toBe(Invoice::STATUS_PARTIAL)
        ->and($invoice->paid_amount)->toBe('500.00')
        ->and($invoice->dueAmount())->toBe(300.0);
});

it('pays the oldest dues first', function () {
    $setup = feeSetup(feeTenant());
    $student = feeStudent($setup);

    $generator = app(InvoiceGenerator::class);
    $generator->run($setup['session'], '2026-01');
    $generator->run($setup['session'], '2026-02');

    // এক মাসের টাকা — পুরনোটাই আগে শোধ হওয়া চাই।
    app(PaymentCollector::class)->collect($student, $setup['session']->id, 800);

    expect(Invoice::where('billing_month', '2026-01')->first()->status)
        ->toBe(Invoice::STATUS_PAID)
        ->and(Invoice::where('billing_month', '2026-02')->first()->status)
        ->toBe(Invoice::STATUS_UNPAID);
});

it('keeps an overpayment as an unallocated advance', function () {
    $setup = feeSetup(feeTenant());
    $student = feeStudent($setup);

    app(InvoiceGenerator::class)->run($setup['session'], '2026-01');

    $payment = app(PaymentCollector::class)
        ->collect($student, $setup['session']->id, 1000);

    // ৮০০ বিলে বসেছে, ২০০ অগ্রিম জমা।
    expect($payment->allocatedAmount())->toBe(800.0)
        ->and($payment->unallocatedAmount())->toBe(200.0);
});

// ---- বাতিল ----

it('cancels a receipt without deleting it', function () {
    $setup = feeSetup(feeTenant());
    $student = feeStudent($setup);

    app(InvoiceGenerator::class)->run($setup['session'], '2026-01');

    $collector = app(PaymentCollector::class);
    $payment = $collector->collect($student, $setup['session']->id, 800);

    expect(Invoice::firstOrFail()->status)->toBe(Invoice::STATUS_PAID);

    $collector->cancel($payment, 'ভুল অঙ্কে কাটা হয়েছিল');

    $invoice = Invoice::firstOrFail();

    // রসিদ থেকে যায় — অভিভাবকের হাতে কাগজের কপি আছে।
    expect($payment->fresh()->isCancelled())->toBeTrue()
        ->and($payment->fresh()->cancel_reason)->toBe('ভুল অঙ্কে কাটা হয়েছিল')
        ->and($payment->fresh()->allocations()->count())->toBe(0)
        // ইনভয়েস আবার বকেয়া।
        ->and($invoice->status)->toBe(Invoice::STATUS_UNPAID)
        ->and($invoice->dueAmount())->toBe(800.0);
});

// ---- টেন্যান্ট আলাদা ----

it('keeps invoices of one madrasa invisible to another', function () {
    $first = feeTenant('darul-ulum');
    $setup = feeSetup($first);
    feeStudent($setup);
    app(InvoiceGenerator::class)->run($setup['session'], '2026-01');
    tenancy()->end();

    $second = feeTenant('jamia-islamia');
    tenancy()->initialize($second);

    expect(Invoice::count())->toBe(0);
});

// ---- স্ক্রিন ----

it('shows the fee structure screen', function () {
    $tenant = feeTenant();

    $this->actingAs(feeAdmin($tenant))
        ->get('http://darul-ulum.localhost/panel/finance/fee-structures')
        ->assertOk()
        ->assertSee('ফি');
});

it('shows the collection screen', function () {
    $tenant = feeTenant();

    $this->actingAs(feeAdmin($tenant))
        ->get('http://darul-ulum.localhost/panel/finance/collect')
        ->assertOk();
});

it('generates invoices from the bill run screen', function () {
    $tenant = feeTenant();
    $setup = feeSetup($tenant);
    feeStudent($setup);
    $user = feeAdmin($tenant);
    tenancy()->initialize($tenant);
    // SetCurrentAcademicSession middleware Livewire::test()-এ চলে না।
    app(CurrentSession::class)->set($setup['session']);

    Livewire::actingAs($user)
        ->test(InvoiceRun::class)
        ->set('billingMonth', '2026-01')
        ->call('generate')
        ->assertHasNoErrors();

    expect(Invoice::count())->toBe(1);
});

it('collects a payment from the screen', function () {
    $tenant = feeTenant();
    $setup = feeSetup($tenant);
    $student = feeStudent($setup);
    app(InvoiceGenerator::class)->run($setup['session'], '2026-01');
    $user = feeAdmin($tenant);
    tenancy()->initialize($tenant);
    app(CurrentSession::class)->set($setup['session']);

    Livewire::actingAs($user)
        ->test(PaymentCollect::class)
        ->call('selectStudent', $student->id)
        ->set('amount', '800')
        ->call('save')
        ->assertHasNoErrors();

    expect(Payment::count())->toBe(1)
        ->and(Invoice::firstOrFail()->status)->toBe(Invoice::STATUS_PAID);
});

// ---- রসিদ PDF ----

it('renders a receipt pdf inside tenant context', function () {
    $tenant = feeTenant();
    $setup = feeSetup($tenant);
    $student = feeStudent($setup);
    app(InvoiceGenerator::class)->run($setup['session'], '2026-01');

    $payment = app(PaymentCollector::class)
        ->collect($student, $setup['session']->id, 800);

    $bytes = app(ReceiptPdf::class)->render($payment);

    // ফন্ট storage_path() থেকে নিলে টেন্যান্ট কনটেক্সটে ভেঙে যেত।
    expect(tenancy()->initialized)->toBeTrue()
        ->and($bytes)->toStartWith('%PDF-')
        ->and(strlen($bytes))->toBeGreaterThan(1000);
});

it('still renders a receipt after cancellation', function () {
    $tenant = feeTenant();
    $setup = feeSetup($tenant);
    $student = feeStudent($setup);
    app(InvoiceGenerator::class)->run($setup['session'], '2026-01');

    $collector = app(PaymentCollector::class);
    $payment = $collector->collect($student, $setup['session']->id, 800);
    $collector->cancel($payment, 'ভুল অঙ্ক');

    expect(app(ReceiptPdf::class)->render($payment->fresh()))->toStartWith('%PDF-');
});

it('downloads the receipt over http', function () {
    $tenant = feeTenant();
    $setup = feeSetup($tenant);
    $student = feeStudent($setup);
    app(InvoiceGenerator::class)->run($setup['session'], '2026-01');
    $payment = app(PaymentCollector::class)->collect($student, $setup['session']->id, 800);
    tenancy()->end();

    $this->actingAs(feeAdmin($tenant))
        ->get("http://darul-ulum.localhost/panel/finance/payments/{$payment->id}/receipt.pdf")
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

it('hides another madrasa receipt', function () {
    $first = feeTenant('darul-ulum');
    $setup = feeSetup($first);
    $student = feeStudent($setup);
    app(InvoiceGenerator::class)->run($setup['session'], '2026-01');
    $payment = app(PaymentCollector::class)->collect($student, $setup['session']->id, 800);
    tenancy()->end();

    $second = feeTenant('jamia-islamia');

    $this->actingAs(feeAdmin($second))
        ->get("http://jamia-islamia.localhost/panel/finance/payments/{$payment->id}/receipt.pdf")
        ->assertNotFound();
});

// ---- livewire/update-এ চলতি বর্ষ ----

it('keeps the current session on the livewire update route', function () {
    // পেজ লোড tenant.panel গ্রুপ দিয়ে বর্ষ পায়, কিন্তু livewire/update
    // আলাদা রুট — সেখানে middleware না থাকলে প্রতি wire:click-এ বর্ষ
    // হারিয়ে যেত আর ফি স্ক্রিন "চলতি শিক্ষাবর্ষ নেই" বলত।
    $middleware = collect(app('router')->getRoutes()->getRoutes())
        ->first(fn ($route) => $route->uri() === 'livewire/update')
        ?->gatherMiddleware() ?? [];

    expect($middleware)->toContain(SetCurrentAcademicSession::class);
});

it('saves a fee structure through a livewire request', function () {
    $tenant = feeTenant();
    tenancy()->initialize($tenant);

    $session = AcademicSession::create(['name' => '১৪৪৬ হিজরি', 'is_current' => true]);
    $marhala = Marhala::where('code', 'hifz')->firstOrFail();
    $jamaat = Jamaat::create(['marhala_id' => $marhala->id, 'name' => 'কায়দা']);
    $feeHead = FeeHead::where('code', 'monthly_fee')->firstOrFail();

    $user = User::query()->where('tenant_id', $tenant->getKey())->firstOrFail();

    // মিডলওয়্যার যা করে — বর্ষ বসিয়ে দেওয়া।
    app(CurrentSession::class)->set($session);

    Livewire::actingAs($user)
        ->test(FeeStructureList::class)
        ->set('jamaatId', (string) $jamaat->id)
        ->set('feeHeadId', (string) $feeHead->id)
        ->set('amount', '750')
        ->call('save')
        ->assertHasNoErrors();

    expect(FeeStructure::count())->toBe(1)
        ->and(FeeStructure::first()->amount)->toBe('750.00');
});

// ---- একাধিক খাত এক বিলে ----

it('bills seat rent alongside the monthly fee', function () {
    $tenant = feeTenant();
    $setup = feeSetup($tenant);
    feeStudent($setup);

    // সিট ভাড়া boarding টাইপ — এটিও প্রতি মাসে আসা চাই।
    $seat = FeeHead::where('code', 'seat_rent')->firstOrFail();

    FeeStructure::create([
        'academic_session_id' => $setup['session']->id,
        'jamaat_id' => $setup['jamaat']->id,
        'fee_head_id' => $seat->id,
        'amount' => 100,
    ]);

    app(InvoiceGenerator::class)->run($setup['session'], '2026-01');

    $invoice = Invoice::with('lines')->firstOrFail();

    expect($invoice->lines)->toHaveCount(2)
        ->and($invoice->net_amount)->toBe('900.00');
});

it('leaves exam fees out until they are picked', function () {
    $tenant = feeTenant();
    $setup = feeSetup($tenant);
    feeStudent($setup);

    $exam = FeeHead::where('code', 'exam_fee')->firstOrFail();

    FeeStructure::create([
        'academic_session_id' => $setup['session']->id,
        'jamaat_id' => $setup['jamaat']->id,
        'fee_head_id' => $exam->id,
        'amount' => 200,
    ]);

    $generator = app(InvoiceGenerator::class);

    // ডিফল্ট রান — পরীক্ষার ফি আসবে না।
    $generator->run($setup['session'], '2026-01');
    expect(Invoice::firstOrFail()->net_amount)->toBe('800.00');

    // হাতে বেছে দিলে আসবে।
    $generator->run($setup['session'], '2026-02', [
        $setup['feeHead']->id,
        $exam->id,
    ]);

    expect(Invoice::where('billing_month', '2026-02')->firstOrFail()->net_amount)
        ->toBe('1000.00');
});

it('refuses a run with no fee head selected', function () {
    $tenant = feeTenant();
    $setup = feeSetup($tenant);
    feeStudent($setup);
    $user = feeAdmin($tenant);
    tenancy()->initialize($tenant);
    app(CurrentSession::class)->set($setup['session']);

    Livewire::actingAs($user)
        ->test(InvoiceRun::class)
        ->set('selectedHeads', [])
        ->call('generate');

    expect(Invoice::count())->toBe(0);
});
