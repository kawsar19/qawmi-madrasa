<?php

declare(strict_types=1);

use App\Livewire\Tenant\People\StudentList;
use App\Models\Academic\AcademicSession;
use App\Models\Academic\Jamaat;
use App\Models\Academic\Marhala;
use App\Models\Central\Tenant;
use App\Models\People\Enrollment;
use App\Models\People\Guardian;
use App\Models\People\Student;
use App\Models\User;
use App\Services\People\StudentRegistrar;
use App\Services\Tenancy\TenantProvisioner;
use Illuminate\Database\QueryException;
use Livewire\Livewire;

afterEach(fn () => tenancy()->end());

function studentTenant(string $slug = 'darul-ulum'): Tenant
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

function studentAdmin(Tenant $tenant): User
{
    tenancy()->initialize($tenant);
    $user = User::query()->where('tenant_id', $tenant->getKey())->firstOrFail();
    tenancy()->end();

    return $user;
}

/** চলতি বর্ষ + একটি ক্লাস — ভর্তির ন্যূনতম শর্ত। */
function studentSetup(Tenant $tenant): Jamaat
{
    tenancy()->initialize($tenant);

    AcademicSession::create(['name' => '১৪৪৬-১৪৪৭ হিজরি', 'is_current' => true]);

    $marhala = Marhala::where('code', 'hifz')->firstOrFail();

    return Jamaat::create(['marhala_id' => $marhala->id, 'name' => '১০ পারা']);
}

it('shows the student screen', function () {
    $tenant = studentTenant();

    $this->actingAs(studentAdmin($tenant))
        ->get('http://darul-ulum.localhost/panel/people/students')
        ->assertOk()
        ->assertSee('ছাত্র')
        ->assertSee('এখনো কোনো ছাত্র নেই।');
});

it('admits a student with a uid, guardian and enrollment', function () {
    $tenant = studentTenant();
    $user = studentAdmin($tenant);
    $jamaat = studentSetup($tenant);

    Livewire::actingAs($user)
        ->test(StudentList::class)
        ->set('name', 'আব্দুল্লাহ')
        ->set('fatherName', 'আব্দুর রহমান')
        ->set('district', 'কুমিল্লা')
        ->set('upazila', 'চান্দিনা')
        ->set('union', 'বরকইট')
        ->set('village', 'মাধাইয়া')
        ->set('guardianName', 'আব্দুর রহমান')
        ->set('guardianRelation', 'father')
        ->set('guardianMobile', '01712345678')
        ->set('jamaatId', (string) $jamaat->id)
        ->call('save')
        ->assertHasNoErrors();

    $student = Student::firstOrFail();
    $enrollment = Enrollment::firstOrFail();

    expect($student->student_uid)->not->toBe('')
        ->and($student->district)->toBe('কুমিল্লা')
        ->and($student->upazila)->toBe('চান্দিনা')
        ->and($student->guardians)->toHaveCount(1)
        ->and($enrollment->student_id)->toBe($student->id)
        ->and($enrollment->jamaat_id)->toBe($jamaat->id)
        ->and($enrollment->roll_no)->toBe(1);
});

it('gives each student a distinct uid', function () {
    $tenant = studentTenant();
    $user = studentAdmin($tenant);
    $jamaat = studentSetup($tenant);

    foreach (['আব্দুল্লাহ', 'মুহাম্মদ'] as $name) {
        Livewire::actingAs($user)
            ->test(StudentList::class)
            ->set('name', $name)
            ->set('fatherName', 'পিতা')
            ->set('jamaatId', (string) $jamaat->id)
            ->call('save')
            ->assertHasNoErrors();
    }

    expect(Student::pluck('student_uid')->unique())->toHaveCount(2);
});

it('numbers rolls sequentially within a class', function () {
    $tenant = studentTenant();
    $user = studentAdmin($tenant);
    $jamaat = studentSetup($tenant);

    foreach (['ক', 'খ', 'গ'] as $name) {
        Livewire::actingAs($user)
            ->test(StudentList::class)
            ->set('name', $name)
            ->set('fatherName', 'পিতা')
            ->set('jamaatId', (string) $jamaat->id)
            ->call('save');
    }

    expect(Enrollment::orderBy('id')->pluck('roll_no')->all())->toBe([1, 2, 3]);
});

it('restarts roll numbers in a different class', function () {
    $tenant = studentTenant();
    $user = studentAdmin($tenant);
    $first = studentSetup($tenant);

    $second = Jamaat::create(['marhala_id' => $first->marhala_id, 'name' => '২০ পারা']);

    foreach ([$first, $second] as $jamaat) {
        Livewire::actingAs($user)
            ->test(StudentList::class)
            ->set('name', 'ছাত্র '.$jamaat->id)
            ->set('fatherName', 'পিতা')
            ->set('jamaatId', (string) $jamaat->id)
            ->call('save');
    }

    // Rolls are scoped to session + class, so each class starts at 1.
    expect(Enrollment::where('jamaat_id', $first->id)->value('roll_no'))->toBe(1)
        ->and(Enrollment::where('jamaat_id', $second->id)->value('roll_no'))->toBe(1);
});

it('honours a manually given roll', function () {
    $tenant = studentTenant();
    $user = studentAdmin($tenant);
    $jamaat = studentSetup($tenant);

    Livewire::actingAs($user)
        ->test(StudentList::class)
        ->set('name', 'ছাত্র')
        ->set('fatherName', 'পিতা')
        ->set('jamaatId', (string) $jamaat->id)
        ->set('rollNo', '25')
        ->call('save')
        ->assertHasNoErrors();

    expect(Enrollment::firstOrFail()->roll_no)->toBe(25);
});

it('reuses one guardian for siblings sharing a mobile number', function () {
    $tenant = studentTenant();
    $user = studentAdmin($tenant);
    $jamaat = studentSetup($tenant);

    foreach (['বড় ভাই', 'ছোট ভাই'] as $name) {
        Livewire::actingAs($user)
            ->test(StudentList::class)
            ->set('name', $name)
            ->set('fatherName', 'আব্দুর রহমান')
            ->set('guardianName', 'আব্দুর রহমান')
            ->set('guardianMobile', '01712345678')
            ->set('jamaatId', (string) $jamaat->id)
            ->call('save')
            ->assertHasNoErrors();
    }

    // Mobile numbers are not unique in this domain; siblings share one, and
    // the guardian must not be duplicated because of it.
    expect(Guardian::count())->toBe(1)
        ->and(Guardian::firstOrFail()->students)->toHaveCount(2);
});

it('refuses to admit without a current academic session', function () {
    $tenant = studentTenant();
    $user = studentAdmin($tenant);

    tenancy()->initialize($tenant);
    $marhala = Marhala::where('code', 'hifz')->firstOrFail();
    $jamaat = Jamaat::create(['marhala_id' => $marhala->id, 'name' => '১০ পারা']);

    Livewire::actingAs($user)
        ->test(StudentList::class)
        ->set('name', 'ছাত্র')
        ->set('fatherName', 'পিতা')
        ->set('jamaatId', (string) $jamaat->id)
        ->call('save');

    // Rolls and results hang off the session, so admitting without one would
    // leave an enrollment that no report could ever find.
    expect(Student::count())->toBe(0);
});

it('requires name, father and class', function () {
    $tenant = studentTenant();
    $user = studentAdmin($tenant);
    studentSetup($tenant);

    Livewire::actingAs($user)
        ->test(StudentList::class)
        ->call('save')
        ->assertHasErrors(['name', 'fatherName', 'jamaatId']);
});

it('rolls back the whole admission when enrollment fails', function () {
    $tenant = studentTenant();
    studentSetup($tenant);

    $registrar = app(StudentRegistrar::class);

    // A non-existent class violates the foreign key mid-transaction.
    try {
        $registrar->register(
            ['name' => 'ছাত্র', 'father_name' => 'পিতা'],
            null,
            ['academic_session_id' => AcademicSession::firstOrFail()->id, 'jamaat_id' => 99999],
        );
        $this->fail('এনরোলমেন্ট ব্যর্থ হওয়ার কথা ছিল।');
    } catch (QueryException) {
        // expected
    }

    // Student, uid and enrollment are one transaction — no orphan row.
    expect(Student::count())->toBe(0)
        ->and(Enrollment::count())->toBe(0);
});

it('searches by name, uid and father', function () {
    $tenant = studentTenant();
    $user = studentAdmin($tenant);
    $jamaat = studentSetup($tenant);

    app(StudentRegistrar::class)->register(
        ['name' => 'আব্দুল্লাহ', 'father_name' => 'রহমান'],
        null,
        ['academic_session_id' => AcademicSession::firstOrFail()->id, 'jamaat_id' => $jamaat->id],
    );
    app(StudentRegistrar::class)->register(
        ['name' => 'ইব্রাহিম', 'father_name' => 'করিম'],
        null,
        ['academic_session_id' => AcademicSession::firstOrFail()->id, 'jamaat_id' => $jamaat->id],
    );

    Livewire::actingAs($user)
        ->test(StudentList::class)
        ->set('search', 'আব্দুল্লাহ')
        ->assertSee('আব্দুল্লাহ')
        ->assertDontSee('ইব্রাহিম');

    Livewire::actingAs($user)
        ->test(StudentList::class)
        ->set('search', 'করিম')
        ->assertSee('ইব্রাহিম')
        ->assertDontSee('আব্দুল্লাহ');
});

it('edits a student without touching the enrollment', function () {
    $tenant = studentTenant();
    $user = studentAdmin($tenant);
    $jamaat = studentSetup($tenant);

    $student = app(StudentRegistrar::class)->register(
        ['name' => 'আব্দুল্লাহ', 'father_name' => 'রহমান'],
        null,
        ['academic_session_id' => AcademicSession::firstOrFail()->id, 'jamaat_id' => $jamaat->id],
    );

    Livewire::actingAs($user)
        ->test(StudentList::class)
        ->call('edit', $student->id)
        ->set('name', 'আব্দুল্লাহ আল-মামুন')
        ->set('district', 'নোয়াখালী')
        ->call('save')
        ->assertHasNoErrors();

    expect($student->refresh()->name)->toBe('আব্দুল্লাহ আল-মামুন')
        ->and($student->district)->toBe('নোয়াখালী')
        ->and(Enrollment::count())->toBe(1);
});

it('never shows another madrasa\'s students', function () {
    $a = studentTenant('madrasa-a');
    $b = studentTenant('madrasa-b');

    $jamaat = studentSetup($a);
    app(StudentRegistrar::class)->register(
        ['name' => 'ক-মাদরাসার ছাত্র', 'father_name' => 'পিতা'],
        null,
        ['academic_session_id' => AcademicSession::firstOrFail()->id, 'jamaat_id' => $jamaat->id],
    );
    tenancy()->end();

    $this->actingAs(studentAdmin($b))
        ->get('http://madrasa-b.localhost/panel/people/students')
        ->assertOk()
        ->assertDontSee('ক-মাদরাসার ছাত্র');
});

it('counts students on the dashboard', function () {
    $tenant = studentTenant();
    $jamaat = studentSetup($tenant);

    app(StudentRegistrar::class)->register(
        ['name' => 'ছাত্র', 'father_name' => 'পিতা'],
        null,
        ['academic_session_id' => AcademicSession::firstOrFail()->id, 'jamaat_id' => $jamaat->id],
    );
    tenancy()->end();

    $this->actingAs(studentAdmin($tenant))
        ->get('http://darul-ulum.localhost/panel')
        ->assertOk()
        ->assertSee('মোট ছাত্র');
});
