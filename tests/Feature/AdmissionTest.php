<?php

declare(strict_types=1);

use App\Livewire\Tenant\People\AdmissionList;
use App\Models\Academic\AcademicSession;
use App\Models\Academic\Jamaat;
use App\Models\Academic\Marhala;
use App\Models\Central\Tenant;
use App\Models\People\Admission;
use App\Models\People\Enrollment;
use App\Models\People\Guardian;
use App\Models\People\Student;
use App\Models\User;
use App\Services\People\AdmissionService;
use App\Services\Tenancy\TenantProvisioner;
use Livewire\Livewire;

afterEach(fn () => tenancy()->end());

function admissionTenant(string $slug = 'darul-ulum'): Tenant
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

function admissionAdmin(Tenant $tenant): User
{
    tenancy()->initialize($tenant);
    $user = User::query()->where('tenant_id', $tenant->getKey())->firstOrFail();
    tenancy()->end();

    return $user;
}

/** টেন্যান্ট কনটেক্সট সহ অ্যাডমিন — Livewire::test() নিজে tenancy চালু করে না। */
function admissionActor(Tenant $tenant): User
{
    $user = admissionAdmin($tenant);
    tenancy()->initialize($tenant);

    return $user;
}

/** চলতি বর্ষ + একটি ক্লাস — আবেদনের ন্যূনতম শর্ত। */
function admissionSetup(Tenant $tenant): Jamaat
{
    tenancy()->initialize($tenant);

    AcademicSession::create(['name' => '১৪৪৬-১৪৪৭ হিজরি', 'is_current' => true]);

    $marhala = Marhala::where('code', 'hifz')->firstOrFail();

    return Jamaat::create(['marhala_id' => $marhala->id, 'name' => '১০ পারা']);
}

/** @return array{0: Admission, 1: Jamaat} */
function makeApplication(Tenant $tenant, array $overrides = []): array
{
    $jamaat = admissionSetup($tenant);

    $admission = app(AdmissionService::class)->apply([
        'academic_session_id' => AcademicSession::firstOrFail()->id,
        'jamaat_id' => $jamaat->id,
        'name' => 'আব্দুল্লাহ',
        'father_name' => 'আব্দুর রহমান',
        'guardian_name' => 'আব্দুর রহমান',
        'guardian_mobile' => '01712345678',
        ...$overrides,
    ]);

    return [$admission, $jamaat];
}

it('shows the admission screen', function () {
    $tenant = admissionTenant();

    $this->actingAs(admissionAdmin($tenant))
        ->get('http://darul-ulum.localhost/panel/people/admissions')
        ->assertOk()
        ->assertSee('ভর্তি')
        ->assertSee('এখনো কোনো আবেদন নেই।');
});

it('takes an application with a numbered slip', function () {
    $tenant = admissionTenant();
    $user = admissionActor($tenant);
    $jamaat = admissionSetup($tenant);

    Livewire::actingAs($user)
        ->test(AdmissionList::class)
        ->set('name', 'আব্দুল্লাহ')
        ->set('fatherName', 'আব্দুর রহমান')
        ->set('district', 'কুমিল্লা')
        ->set('previousMadrasa', 'নূরানী মাদরাসা')
        ->set('guardianName', 'আব্দুর রহমান')
        ->set('guardianMobile', '01712345678')
        ->set('jamaatId', (string) $jamaat->id)
        ->call('save')
        ->assertHasNoErrors();

    $admission = Admission::firstOrFail();

    expect($admission->application_no)->not->toBe('')
        ->and($admission->status)->toBe(Admission::STATUS_APPLIED)
        ->and($admission->source)->toBe(Admission::SOURCE_OFFICE)
        ->and($admission->previous_madrasa)->toBe('নূরানী মাদরাসা')
        ->and($admission->applied_on)->not->toBeNull()
        // আবেদনকারী এখনো ছাত্র নয়।
        ->and($admission->student_id)->toBeNull()
        ->and(Student::count())->toBe(0);
});

it('does not create a student until enrolment', function () {
    $tenant = admissionTenant();
    admissionActor($tenant);
    [$admission] = makeApplication($tenant);

    $service = app(AdmissionService::class);

    expect(Student::count())->toBe(0);

    $service->approve($admission);

    // অনুমোদনেও ছাত্র তৈরি হয় না — নইলে বাতিল হওয়া আবেদন গণনায় ঢুকত।
    expect($admission->refresh()->status)->toBe(Admission::STATUS_APPROVED)
        ->and(Student::count())->toBe(0);
});

it('enrols an approved application in one click', function () {
    $tenant = admissionTenant();
    $user = admissionActor($tenant);
    [$admission, $jamaat] = makeApplication($tenant);

    app(AdmissionService::class)->approve($admission);

    Livewire::actingAs($user)
        ->test(AdmissionList::class)
        ->call('startEnroll', $admission->id)
        ->call('enroll')
        ->assertHasNoErrors();

    $student = Student::firstOrFail();
    $enrollment = Enrollment::firstOrFail();

    // ছাত্র + UID + অভিভাবক + এনরোলমেন্ট + রোল — সব এক ধাপে।
    expect($student->name)->toBe('আব্দুল্লাহ')
        ->and($student->student_uid)->not->toBe('')
        ->and($student->guardians)->toHaveCount(1)
        ->and($enrollment->jamaat_id)->toBe($jamaat->id)
        ->and($enrollment->roll_no)->toBe(1)
        ->and($admission->refresh()->status)->toBe(Admission::STATUS_ENROLLED)
        ->and($admission->student_id)->toBe($student->id);
});

it('keeps the row visible after a decision moves it', function () {
    $tenant = admissionTenant();
    $user = admissionActor($tenant);
    [$admission] = makeApplication($tenant);

    // ডিফল্ট ফিল্টার "আবেদিত"; অনুমোদনের পর সারিটা ঐ তালিকায় থাকে না।
    // ফিল্টার না সরালে ব্যবহারকারী "ভর্তি করুন" বাটনটাই খুঁজে পাবেন না।
    $component = Livewire::actingAs($user)
        ->test(AdmissionList::class)
        ->assertSet('filterStatus', Admission::STATUS_APPLIED)
        ->call('approve', $admission->id)
        ->assertSet('filterStatus', Admission::STATUS_APPROVED)
        ->assertSee('আব্দুল্লাহ');

    $component->call('startEnroll', $admission->id)
        ->call('enroll')
        ->assertSet('filterStatus', Admission::STATUS_ENROLLED)
        ->assertSee('আব্দুল্লাহ');
});

it('honours a manually given roll at enrolment', function () {
    $tenant = admissionTenant();
    $user = admissionActor($tenant);
    [$admission] = makeApplication($tenant);

    app(AdmissionService::class)->approve($admission);

    Livewire::actingAs($user)
        ->test(AdmissionList::class)
        ->call('startEnroll', $admission->id)
        ->set('enrollRollNo', '25')
        ->call('enroll')
        ->assertHasNoErrors();

    expect(Enrollment::firstOrFail()->roll_no)->toBe(25);
});

it('copies the applicant details onto the student', function () {
    $tenant = admissionTenant();
    admissionActor($tenant);

    [$admission] = makeApplication($tenant, [
        'name_ar' => 'عبد الله',
        'mother_name' => 'আমেনা',
        'district' => 'কুমিল্লা',
        'upazila' => 'চান্দিনা',
        'residency_type' => Student::RESIDENCY_RESIDENTIAL,
        'is_orphan' => true,
    ]);

    $service = app(AdmissionService::class);
    $service->approve($admission);
    $student = $service->enroll($admission);

    expect($student->name_ar)->toBe('عبد الله')
        ->and($student->mother_name)->toBe('আমেনা')
        ->and($student->district)->toBe('কুমিল্লা')
        ->and($student->upazila)->toBe('চান্দিনা')
        ->and($student->residency_type)->toBe(Student::RESIDENCY_RESIDENTIAL)
        ->and($student->is_orphan)->toBeTrue()
        ->and($student->admitted_on)->not->toBeNull();
});

it('refuses to enrol an application that was never approved', function () {
    $tenant = admissionTenant();
    admissionActor($tenant);
    [$admission] = makeApplication($tenant);

    expect(fn () => app(AdmissionService::class)->enroll($admission))
        ->toThrow(RuntimeException::class);

    expect(Student::count())->toBe(0);
});

it('refuses to enrol a rejected application', function () {
    $tenant = admissionTenant();
    admissionActor($tenant);
    [$admission] = makeApplication($tenant);

    $service = app(AdmissionService::class);
    $service->reject($admission);

    expect(fn () => $service->enroll($admission))->toThrow(RuntimeException::class);
    expect(Student::count())->toBe(0);
});

it('never enrols the same application twice', function () {
    $tenant = admissionTenant();
    admissionActor($tenant);
    [$admission] = makeApplication($tenant);

    $service = app(AdmissionService::class);
    $service->approve($admission);
    $service->enroll($admission);

    // দুবার চাপলে দুটো ছাত্র তৈরি হওয়া চলবে না।
    expect(fn () => $service->enroll($admission))->toThrow(RuntimeException::class);
    expect(Student::count())->toBe(1)
        ->and(Enrollment::count())->toBe(1);
});

it('refuses to decide an application twice', function () {
    $tenant = admissionTenant();
    admissionActor($tenant);
    [$admission] = makeApplication($tenant);

    $service = app(AdmissionService::class);
    $service->approve($admission);

    expect(fn () => $service->reject($admission))->toThrow(RuntimeException::class);
    expect($admission->refresh()->status)->toBe(Admission::STATUS_APPROVED);
});

it('records who decided and when', function () {
    $tenant = admissionTenant();
    $user = admissionActor($tenant);
    [$admission] = makeApplication($tenant);

    $this->actingAs($user);
    app(AdmissionService::class)->approve($admission);

    expect($admission->refresh()->decided_by)->toBe($user->id)
        ->and($admission->decided_at)->not->toBeNull();
});

it('reuses one guardian for siblings sharing a mobile number', function () {
    $tenant = admissionTenant();
    admissionActor($tenant);

    $jamaat = admissionSetup($tenant);
    $service = app(AdmissionService::class);

    foreach (['বড় ভাই', 'ছোট ভাই'] as $name) {
        $admission = $service->apply([
            'academic_session_id' => AcademicSession::firstOrFail()->id,
            'jamaat_id' => $jamaat->id,
            'name' => $name,
            'father_name' => 'আব্দুর রহমান',
            'guardian_name' => 'আব্দুর রহমান',
            'guardian_mobile' => '01712345678',
        ]);

        $service->approve($admission);
        $service->enroll($admission);
    }

    // ভাইবোন এক মোবাইল ভাগ করে — অভিভাবক দুবার তৈরি হবে না।
    expect(Guardian::count())->toBe(1)
        ->and(Guardian::firstOrFail()->students)->toHaveCount(2);
});

it('numbers applications sequentially within a session', function () {
    $tenant = admissionTenant();
    admissionActor($tenant);

    $jamaat = admissionSetup($tenant);
    $service = app(AdmissionService::class);

    foreach (['ক', 'খ', 'গ'] as $name) {
        $service->apply([
            'academic_session_id' => AcademicSession::firstOrFail()->id,
            'jamaat_id' => $jamaat->id,
            'name' => $name,
            'father_name' => 'পিতা',
        ]);
    }

    expect(Admission::orderBy('id')->pluck('application_no')->all())
        ->toBe(['0001', '0002', '0003']);
});

it('requires name, father and class', function () {
    $tenant = admissionTenant();
    $user = admissionActor($tenant);
    admissionSetup($tenant);

    Livewire::actingAs($user)
        ->test(AdmissionList::class)
        ->call('save')
        ->assertHasErrors(['name', 'fatherName', 'jamaatId']);
});

it('refuses to take an application without a current session', function () {
    $tenant = admissionTenant();
    $user = admissionActor($tenant);

    $marhala = Marhala::where('code', 'hifz')->firstOrFail();
    $jamaat = Jamaat::create(['marhala_id' => $marhala->id, 'name' => '১০ পারা']);

    Livewire::actingAs($user)
        ->test(AdmissionList::class)
        ->set('name', 'আব্দুল্লাহ')
        ->set('fatherName', 'পিতা')
        ->set('jamaatId', (string) $jamaat->id)
        ->call('save');

    expect(Admission::count())->toBe(0);
});

it('filters by status', function () {
    $tenant = admissionTenant();
    $user = admissionActor($tenant);

    $jamaat = admissionSetup($tenant);
    $service = app(AdmissionService::class);

    $pending = $service->apply([
        'academic_session_id' => AcademicSession::firstOrFail()->id,
        'jamaat_id' => $jamaat->id,
        'name' => 'অপেক্ষমাণ ছাত্র',
        'father_name' => 'পিতা',
    ]);

    $rejected = $service->apply([
        'academic_session_id' => AcademicSession::firstOrFail()->id,
        'jamaat_id' => $jamaat->id,
        'name' => 'বাতিল ছাত্র',
        'father_name' => 'পিতা',
    ]);
    $service->reject($rejected);

    Livewire::actingAs($user)
        ->test(AdmissionList::class)
        ->set('filterStatus', Admission::STATUS_APPLIED)
        ->assertSee('অপেক্ষমাণ ছাত্র')
        ->assertDontSee('বাতিল ছাত্র');

    expect($pending->refresh()->status)->toBe(Admission::STATUS_APPLIED);
});

it('searches by name, application number and father', function () {
    $tenant = admissionTenant();
    $user = admissionActor($tenant);

    $jamaat = admissionSetup($tenant);
    $service = app(AdmissionService::class);

    foreach ([['আব্দুল্লাহ', 'রহমান'], ['ইব্রাহিম', 'করিম']] as [$name, $father]) {
        $service->apply([
            'academic_session_id' => AcademicSession::firstOrFail()->id,
            'jamaat_id' => $jamaat->id,
            'name' => $name,
            'father_name' => $father,
        ]);
    }

    Livewire::actingAs($user)
        ->test(AdmissionList::class)
        ->set('filterStatus', '')
        ->set('search', 'করিম')
        ->assertSee('ইব্রাহিম')
        ->assertDontSee('আব্দুল্লাহ');
});

it('never shows another madrasa\'s applications', function () {
    $a = admissionTenant('madrasa-a');
    $b = admissionTenant('madrasa-b');

    tenancy()->initialize($a);
    makeApplication($a, ['name' => 'ক-মাদরাসার আবেদনকারী']);
    tenancy()->end();

    $this->actingAs(admissionAdmin($b))
        ->get('http://madrasa-b.localhost/panel/people/admissions')
        ->assertOk()
        ->assertDontSee('ক-মাদরাসার আবেদনকারী');
});
