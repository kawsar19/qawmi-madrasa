<?php

declare(strict_types=1);

use App\Livewire\Tenant\People\EmployeeList;
use App\Models\Academic\Jamaat;
use App\Models\Academic\Marhala;
use App\Models\Academic\Section;
use App\Models\Central\Tenant;
use App\Models\People\Employee;
use App\Models\User;
use App\Services\People\EmployeeRegistrar;
use App\Services\Tenancy\TenantProvisioner;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

afterEach(fn () => tenancy()->end());

function employeeTenant(string $slug = 'darul-ulum'): Tenant
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

function employeeAdmin(Tenant $tenant): User
{
    tenancy()->initialize($tenant);
    $user = User::query()->where('tenant_id', $tenant->getKey())->firstOrFail();
    tenancy()->end();

    return $user;
}

/**
 * টেন্যান্ট কনটেক্সট সহ অ্যাডমিন।
 *
 * Livewire::test() does not run the panel middleware, so nothing initializes
 * tenancy for it — the UID generator and the global scope both need it.
 */
function employeeActor(Tenant $tenant): User
{
    $user = employeeAdmin($tenant);
    tenancy()->initialize($tenant);

    return $user;
}

it('shows the employee screen', function () {
    $tenant = employeeTenant();

    $this->actingAs(employeeAdmin($tenant))
        ->get('http://darul-ulum.localhost/panel/people/employees')
        ->assertOk()
        ->assertSee('শিক্ষক ও কর্মচারী')
        ->assertSee('এখনো কোনো শিক্ষক বা কর্মচারী নেই।');
});

it('hires an employee with a uid', function () {
    $tenant = employeeTenant();
    $user = employeeActor($tenant);

    Livewire::actingAs($user)
        ->test(EmployeeList::class)
        ->set('name', 'মাওলানা আব্দুল করিম')
        ->set('fatherName', 'আব্দুর রহমান')
        ->set('designation', 'ustad')
        ->set('qualification', 'দাওরায়ে হাদিস')
        ->set('monthlySalary', '12000.50')
        ->set('mobile', '01812345678')
        ->set('district', 'কুমিল্লা')
        ->call('save')
        ->assertHasNoErrors();

    $employee = Employee::firstOrFail();

    expect($employee->employee_uid)->not->toBe('')
        ->and($employee->name)->toBe('মাওলানা আব্দুল করিম')
        ->and($employee->type)->toBe(Employee::TYPE_TEACHER)
        ->and($employee->designation)->toBe('ustad')
        ->and($employee->status)->toBe(Employee::STATUS_ACTIVE)
        // টাকা decimal — float নয়, তাই পয়সা হারায় না।
        ->and($employee->monthly_salary)->toBe('12000.50')
        ->and($employee->joined_on)->not->toBeNull()
        ->and($employee->user_id)->toBeNull();
});

it('stores an uploaded photo', function () {
    Storage::fake('public');

    $tenant = employeeTenant();
    $user = employeeActor($tenant);

    Livewire::actingAs($user)
        ->test(EmployeeList::class)
        ->set('name', 'মাওলানা আব্দুল করিম')
        ->set('photo', UploadedFile::fake()->image('ustad.jpg'))
        ->call('save')
        ->assertHasNoErrors();

    $employee = Employee::firstOrFail();

    expect($employee->photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($employee->photo_path);
});

it('replaces the old photo file when a new one is uploaded', function () {
    Storage::fake('public');

    $tenant = employeeTenant();
    $user = employeeActor($tenant);

    Livewire::actingAs($user)
        ->test(EmployeeList::class)
        ->set('name', 'মাওলানা আব্দুল করিম')
        ->set('photo', UploadedFile::fake()->image('old.jpg'))
        ->call('save')
        ->assertHasNoErrors();

    $employee = Employee::firstOrFail();
    $old = $employee->photo_path;

    Livewire::actingAs($user)
        ->test(EmployeeList::class)
        ->call('edit', $employee->id)
        ->set('photo', UploadedFile::fake()->image('new.jpg'))
        ->call('save')
        ->assertHasNoErrors();

    $employee->refresh();

    expect($employee->photo_path)->not->toBe($old);
    // পুরনো ফাইল রেখে দিলে ডিস্ক ভরে যেত।
    Storage::disk('public')->assertMissing($old);
    Storage::disk('public')->assertExists($employee->photo_path);
});

it('keeps the existing photo when none is uploaded on edit', function () {
    Storage::fake('public');

    $tenant = employeeTenant();
    $user = employeeActor($tenant);

    Livewire::actingAs($user)
        ->test(EmployeeList::class)
        ->set('name', 'মাওলানা আব্দুল করিম')
        ->set('photo', UploadedFile::fake()->image('ustad.jpg'))
        ->call('save')
        ->assertHasNoErrors();

    $employee = Employee::firstOrFail();
    $path = $employee->photo_path;

    Livewire::actingAs($user)
        ->test(EmployeeList::class)
        ->call('edit', $employee->id)
        ->set('mobile', '01812345678')
        ->call('save')
        ->assertHasNoErrors();

    expect($employee->refresh()->photo_path)->toBe($path);
    Storage::disk('public')->assertExists($path);
});

it('gives each employee a distinct uid', function () {
    $tenant = employeeTenant();
    $user = employeeActor($tenant);

    foreach (['কারী সাহেব', 'হাফেজ সাহেব'] as $name) {
        Livewire::actingAs($user)
            ->test(EmployeeList::class)
            ->set('name', $name)
            ->call('save')
            ->assertHasNoErrors();
    }

    expect(Employee::pluck('employee_uid')->unique())->toHaveCount(2);
});

it('creates a login account with a role when asked', function () {
    $tenant = employeeTenant();
    $admin = employeeActor($tenant);

    Livewire::actingAs($admin)
        ->test(EmployeeList::class)
        ->set('name', 'মাওলানা ইব্রাহিম')
        ->set('wantsAccount', true)
        ->set('accountEmail', 'ibrahim@darul-ulum.test')
        ->set('accountPassword', 'secret-password')
        ->set('accountRole', 'ustad')
        ->call('save')
        ->assertHasNoErrors();

    $employee = Employee::firstOrFail();
    $account = $employee->user;

    expect($account)->not->toBeNull()
        ->and($account->tenant_id)->toBe($tenant->getKey())
        ->and($account->email)->toBe('ibrahim@darul-ulum.test')
        ->and(Hash::check('secret-password', $account->password))->toBeTrue()
        ->and($account->hasRole('ustad'))->toBeTrue();
});

it('hires a non-teaching staff member without a login', function () {
    $tenant = employeeTenant();
    $user = employeeActor($tenant);

    Livewire::actingAs($user)
        ->test(EmployeeList::class)
        ->set('name', 'বাবুর্চি সাহেব')
        ->set('type', Employee::TYPE_STAFF)
        ->set('designation', 'baburchi')
        ->call('save')
        ->assertHasNoErrors();

    $employee = Employee::firstOrFail();

    // A cook is on the payroll and the attendance register but takes no
    // sections, so no account is created for them.
    expect($employee->type)->toBe(Employee::TYPE_STAFF)
        ->and($employee->isTeacher())->toBeFalse()
        ->and($employee->user_id)->toBeNull();
});

it('requires a name', function () {
    $tenant = employeeTenant();

    Livewire::actingAs(employeeActor($tenant))
        ->test(EmployeeList::class)
        ->call('save')
        ->assertHasErrors(['name']);
});

it('requires email, password and role when an account is asked for', function () {
    $tenant = employeeTenant();

    Livewire::actingAs(employeeActor($tenant))
        ->test(EmployeeList::class)
        ->set('name', 'উস্তাদ')
        ->set('wantsAccount', true)
        ->call('save')
        ->assertHasErrors(['accountEmail', 'accountPassword', 'accountRole']);
});

it('rejects a duplicate login email inside the same madrasa', function () {
    $tenant = employeeTenant();

    // admin@darul-ulum.test আগেই আছে — প্রভিশনিংয়ের সময় তৈরি।
    Livewire::actingAs(employeeActor($tenant))
        ->test(EmployeeList::class)
        ->set('name', 'উস্তাদ')
        ->set('wantsAccount', true)
        ->set('accountEmail', 'admin@darul-ulum.test')
        ->set('accountPassword', 'secret-password')
        ->set('accountRole', 'ustad')
        ->call('save')
        ->assertHasErrors(['accountEmail']);
});

it('allows the same login email in two different madrasas', function () {
    foreach (['madrasa-a', 'madrasa-b'] as $slug) {
        $tenant = employeeTenant($slug);

        tenancy()->initialize($tenant);
        app(EmployeeRegistrar::class)->register(
            ['name' => 'উস্তাদ'],
            ['email' => 'ustad@example.test', 'password' => 'secret-password', 'role' => 'ustad'],
        );
        tenancy()->end();
    }

    // Users are unique per (tenant_id, email) — two madrasas may each have an
    // ustad at the same address.
    expect(User::query()->where('email', 'ustad@example.test')->count())->toBe(2);
});

it('keeps the password when editing without retyping it', function () {
    $tenant = employeeTenant();
    $admin = employeeActor($tenant);

    $employee = app(EmployeeRegistrar::class)->register(
        ['name' => 'উস্তাদ'],
        ['email' => 'ustad@darul-ulum.test', 'password' => 'secret-password', 'role' => 'ustad'],
    );

    Livewire::actingAs($admin)
        ->test(EmployeeList::class)
        ->call('edit', $employee->id)
        ->set('mobile', '01912345678')
        ->call('save')
        ->assertHasNoErrors();

    // Writing the stored hash back would re-hash it through the `hashed`
    // cast and lock the employee out of their own account.
    expect(Hash::check('secret-password', $employee->refresh()->user->password))->toBeTrue()
        ->and($employee->mobile)->toBe('01912345678');
});

it('changes the password when a new one is typed', function () {
    $tenant = employeeTenant();
    $admin = employeeActor($tenant);

    $employee = app(EmployeeRegistrar::class)->register(
        ['name' => 'উস্তাদ'],
        ['email' => 'ustad@darul-ulum.test', 'password' => 'secret-password', 'role' => 'ustad'],
    );

    Livewire::actingAs($admin)
        ->test(EmployeeList::class)
        ->call('edit', $employee->id)
        ->set('accountPassword', 'brand-new-password')
        ->call('save')
        ->assertHasNoErrors();

    expect(Hash::check('brand-new-password', $employee->refresh()->user->password))->toBeTrue();
});

it('deactivates rather than deletes the account when login is revoked', function () {
    $tenant = employeeTenant();
    $admin = employeeActor($tenant);

    $employee = app(EmployeeRegistrar::class)->register(
        ['name' => 'উস্তাদ'],
        ['email' => 'ustad@darul-ulum.test', 'password' => 'secret-password', 'role' => 'ustad'],
    );
    $userId = $employee->user_id;

    Livewire::actingAs($admin)
        ->test(EmployeeList::class)
        ->call('edit', $employee->id)
        ->set('wantsAccount', false)
        ->call('save')
        ->assertHasNoErrors();

    // The activity log and document numbers still reference this user, so the
    // row survives — deactivated, not deleted.
    expect($employee->refresh()->user_id)->toBeNull()
        ->and(User::query()->find($userId))->not->toBeNull()
        ->and(User::query()->find($userId)->is_active)->toBeFalse();
});

it('searches by name, uid and father', function () {
    $tenant = employeeTenant();
    $admin = employeeActor($tenant);

    $registrar = app(EmployeeRegistrar::class);
    $registrar->register(['name' => 'আব্দুল করিম', 'father_name' => 'রহমান']);
    $registrar->register(['name' => 'ইব্রাহিম', 'father_name' => 'হাসান']);

    Livewire::actingAs($admin)
        ->test(EmployeeList::class)
        ->set('search', 'আব্দুল করিম')
        ->assertSee('আব্দুল করিম')
        ->assertDontSee('ইব্রাহিম');

    Livewire::actingAs($admin)
        ->test(EmployeeList::class)
        ->set('search', 'হাসান')
        ->assertSee('ইব্রাহিম')
        ->assertDontSee('আব্দুল করিম');
});

it('filters teachers from staff', function () {
    $tenant = employeeTenant();
    $admin = employeeActor($tenant);

    $registrar = app(EmployeeRegistrar::class);
    $registrar->register(['name' => 'উস্তাদ সাহেব', 'type' => Employee::TYPE_TEACHER]);
    $registrar->register(['name' => 'দারোয়ান সাহেব', 'type' => Employee::TYPE_STAFF]);

    Livewire::actingAs($admin)
        ->test(EmployeeList::class)
        ->set('filterType', Employee::TYPE_TEACHER)
        ->assertSee('উস্তাদ সাহেব')
        ->assertDontSee('দারোয়ান সাহেব');
});

it('hides retired employees behind the default status filter', function () {
    $tenant = employeeTenant();
    $admin = employeeActor($tenant);

    $registrar = app(EmployeeRegistrar::class);
    $registrar->register(['name' => 'কর্মরত সাহেব']);
    $registrar->register(['name' => 'অবসরপ্রাপ্ত সাহেব', 'status' => Employee::STATUS_RETIRED]);

    Livewire::actingAs($admin)
        ->test(EmployeeList::class)
        ->assertSee('কর্মরত সাহেব')
        ->assertDontSee('অবসরপ্রাপ্ত সাহেব');
});

it('assigns an employee as section in-charge', function () {
    $tenant = employeeTenant();

    tenancy()->initialize($tenant);

    $marhala = Marhala::where('code', 'hifz')->firstOrFail();
    $jamaat = Jamaat::create(['marhala_id' => $marhala->id, 'name' => '১০ পারা']);
    $employee = app(EmployeeRegistrar::class)->register(['name' => 'উস্তাদ সাহেব']);

    $section = Section::create([
        'jamaat_id' => $jamaat->id,
        'name' => 'ক',
        'employee_id' => $employee->id,
    ]);

    // sections.employee_id was a bare column until employees existed.
    expect($section->inCharge->name)->toBe('উস্তাদ সাহেব')
        ->and($employee->sections)->toHaveCount(1);
});

it('never shows another madrasa\'s employees', function () {
    $a = employeeTenant('madrasa-a');
    $b = employeeTenant('madrasa-b');

    tenancy()->initialize($a);
    app(EmployeeRegistrar::class)->register(['name' => 'ক-মাদরাসার উস্তাদ']);
    tenancy()->end();

    $this->actingAs(employeeAdmin($b))
        ->get('http://madrasa-b.localhost/panel/people/employees')
        ->assertOk()
        ->assertDontSee('ক-মাদরাসার উস্তাদ');
});

it('counts employees on the dashboard', function () {
    $tenant = employeeTenant();

    tenancy()->initialize($tenant);
    app(EmployeeRegistrar::class)->register(['name' => 'উস্তাদ সাহেব']);
    tenancy()->end();

    $this->actingAs(employeeAdmin($tenant))
        ->get('http://darul-ulum.localhost/panel')
        ->assertOk()
        ->assertSee('শিক্ষক ও কর্মচারী');
});
