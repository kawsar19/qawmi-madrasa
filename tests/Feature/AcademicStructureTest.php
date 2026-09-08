<?php

declare(strict_types=1);

use App\Livewire\Tenant\Academic\JamaatList;
use App\Livewire\Tenant\Academic\MarhalaList;
use App\Models\Academic\Jamaat;
use App\Models\Academic\Marhala;
use App\Models\Academic\Section;
use App\Models\Central\Tenant;
use App\Models\User;
use App\Services\Tenancy\TenantProvisioner;
use Livewire\Livewire;

afterEach(fn () => tenancy()->end());

/**
 * Provisioned rather than factory-made: the screens sit behind
 * `can:academic.*`, and only provisioning creates roles and permissions.
 */
function structureTenant(string $slug = 'darul-ulum'): Tenant
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

function structureAdmin(Tenant $tenant): User
{
    tenancy()->initialize($tenant);
    $user = User::query()->where('tenant_id', $tenant->getKey())->firstOrFail();
    tenancy()->end();

    return $user;
}

// ---------------------------------------------------------------- বিভাগ ----

it('shows the bibhag screen with the seeded marhalas', function () {
    $tenant = structureTenant();

    $this->actingAs(structureAdmin($tenant))
        ->get('http://darul-ulum.localhost/panel/academic/marhalas')
        ->assertOk()
        ->assertSee('বিভাগ')
        ->assertSee('ইবতেদাইয়্যাহ');
});

it('adds a maktab bibhag with a generated code', function () {
    $tenant = structureTenant();
    $user = structureAdmin($tenant);
    tenancy()->initialize($tenant);

    Livewire::actingAs($user)
        ->test(MarhalaList::class)
        ->set('name', 'মক্তব')
        ->set('track', Marhala::TRACK_NAZERA)
        ->set('durationYears', '3')
        ->call('save')
        ->assertHasNoErrors();

    $maktab = Marhala::where('name', 'মক্তব')->firstOrFail();

    // `code` is required and unique but meaningless to staff, so it is
    // derived from the name instead of being asked for.
    expect($maktab->code)->not->toBe('')
        ->and($maktab->track)->toBe(Marhala::TRACK_NAZERA)
        ->and($maktab->duration_years)->toBe(3);
});

it('gives a second bibhag of the same name a distinct code', function () {
    $tenant = structureTenant();
    $user = structureAdmin($tenant);
    tenancy()->initialize($tenant);

    Livewire::actingAs($user)->test(MarhalaList::class)
        ->set('name', 'মক্তব')->call('save')->assertHasNoErrors();

    $first = Marhala::where('name', 'মক্তব')->firstOrFail();
    $first->delete(); // soft-deleted, so its code is still taken

    Livewire::actingAs($user)->test(MarhalaList::class)
        ->set('name', 'মক্তব')->call('save')->assertHasNoErrors();

    $second = Marhala::where('name', 'মক্তব')->firstOrFail();

    expect($second->code)->not->toBe($first->code);
});

it('rejects a duplicate bibhag name', function () {
    $tenant = structureTenant();
    $user = structureAdmin($tenant);
    tenancy()->initialize($tenant);

    Livewire::actingAs($user)
        ->test(MarhalaList::class)
        ->set('name', 'ইবতেদাইয়্যাহ')
        ->call('save')
        ->assertHasErrors(['name' => 'unique']);
});

it('refuses to delete a bibhag that still has classes', function () {
    $tenant = structureTenant();
    $user = structureAdmin($tenant);
    tenancy()->initialize($tenant);

    $marhala = Marhala::create(['code' => 'test', 'name' => 'পরীক্ষামূলক']);
    Jamaat::create(['marhala_id' => $marhala->id, 'name' => 'প্রথম বর্ষ']);

    Livewire::actingAs($user)
        ->test(MarhalaList::class)
        ->call('delete', $marhala->id);

    // Classes cascade at the database level, so this must be blocked.
    expect(Marhala::find($marhala->id))->not->toBeNull();
});

it('refuses to delete a bibhag that still has kitabs', function () {
    $tenant = structureTenant();
    $user = structureAdmin($tenant);
    tenancy()->initialize($tenant);

    $seeded = Marhala::where('code', 'ibtedaiyyah')->firstOrFail();

    Livewire::actingAs($user)
        ->test(MarhalaList::class)
        ->call('delete', $seeded->id);

    expect(Marhala::find($seeded->id))->not->toBeNull();
});

it('deletes an empty bibhag', function () {
    $tenant = structureTenant();
    $user = structureAdmin($tenant);
    tenancy()->initialize($tenant);

    $marhala = Marhala::create(['code' => 'khali', 'name' => 'খালি বিভাগ']);

    Livewire::actingAs($user)
        ->test(MarhalaList::class)
        ->call('delete', $marhala->id);

    expect(Marhala::find($marhala->id))->toBeNull();
});

it('turns a bibhag off and on', function () {
    $tenant = structureTenant();
    $user = structureAdmin($tenant);
    tenancy()->initialize($tenant);

    $marhala = Marhala::where('code', 'hifz')->firstOrFail();
    $component = Livewire::actingAs($user)->test(MarhalaList::class);

    $component->call('toggleActive', $marhala->id);
    expect($marhala->refresh()->is_active)->toBeFalse();

    $component->call('toggleActive', $marhala->id);
    expect($marhala->refresh()->is_active)->toBeTrue();
});

// ----------------------------------------------------------------- ক্লাস ----

it('shows the class screen', function () {
    $tenant = structureTenant();

    $this->actingAs(structureAdmin($tenant))
        ->get('http://darul-ulum.localhost/panel/academic/jamaats')
        ->assertOk()
        ->assertSee('ক্লাস');
});

it('adds hifz classes named by para', function () {
    $tenant = structureTenant();
    $user = structureAdmin($tenant);
    tenancy()->initialize($tenant);

    $hifz = Marhala::where('code', 'hifz')->firstOrFail();

    // Hifz classes are named by progress, not by year — the name is free text.
    foreach (['১০ পারা', '২০ পারা', '৩০ পারা'] as $name) {
        Livewire::actingAs($user)
            ->test(JamaatList::class)
            ->set('marhalaId', (string) $hifz->id)
            ->set('name', $name)
            ->call('save')
            ->assertHasNoErrors();
    }

    expect(Jamaat::where('marhala_id', $hifz->id)->count())->toBe(3)
        ->and(Jamaat::where('name', '২০ পারা')->exists())->toBeTrue();
});

it('orders classes within their bibhag', function () {
    $tenant = structureTenant();
    $user = structureAdmin($tenant);
    tenancy()->initialize($tenant);

    $hifz = Marhala::where('code', 'hifz')->firstOrFail();

    foreach (['১০ পারা', '২০ পারা'] as $name) {
        Livewire::actingAs($user)->test(JamaatList::class)
            ->set('marhalaId', (string) $hifz->id)
            ->set('name', $name)
            ->call('save');
    }

    $orders = Jamaat::where('marhala_id', $hifz->id)->orderBy('id')->pluck('sort_order')->all();

    expect($orders[1])->toBeGreaterThan($orders[0]);
});

it('allows the same class name in two different bibhags', function () {
    $tenant = structureTenant();
    $user = structureAdmin($tenant);
    tenancy()->initialize($tenant);

    $hifz = Marhala::where('code', 'hifz')->firstOrFail();
    $nazera = Marhala::where('code', 'nazera')->firstOrFail();

    foreach ([$hifz, $nazera] as $marhala) {
        Livewire::actingAs($user)
            ->test(JamaatList::class)
            ->set('marhalaId', (string) $marhala->id)
            ->set('name', 'প্রথম বর্ষ')
            ->call('save')
            ->assertHasNoErrors();
    }

    expect(Jamaat::where('name', 'প্রথম বর্ষ')->count())->toBe(2);
});

it('rejects a duplicate class name inside one bibhag', function () {
    $tenant = structureTenant();
    $user = structureAdmin($tenant);
    tenancy()->initialize($tenant);

    $hifz = Marhala::where('code', 'hifz')->firstOrFail();
    Jamaat::create(['marhala_id' => $hifz->id, 'name' => '১০ পারা']);

    Livewire::actingAs($user)
        ->test(JamaatList::class)
        ->set('marhalaId', (string) $hifz->id)
        ->set('name', '১০ পারা')
        ->call('save')
        ->assertHasErrors(['name' => 'unique']);
});

it('requires a bibhag for a class', function () {
    $tenant = structureTenant();
    $user = structureAdmin($tenant);
    tenancy()->initialize($tenant);

    Livewire::actingAs($user)
        ->test(JamaatList::class)
        ->set('name', 'ক্লাস')
        ->call('save')
        ->assertHasErrors(['marhalaId']);
});

it('refuses to delete a class that has sections', function () {
    $tenant = structureTenant();
    $user = structureAdmin($tenant);
    tenancy()->initialize($tenant);

    $hifz = Marhala::where('code', 'hifz')->firstOrFail();
    $jamaat = Jamaat::create(['marhala_id' => $hifz->id, 'name' => '১০ পারা']);
    Section::create(['jamaat_id' => $jamaat->id, 'name' => 'ক']);

    Livewire::actingAs($user)
        ->test(JamaatList::class)
        ->call('delete', $jamaat->id);

    expect(Jamaat::find($jamaat->id))->not->toBeNull();
});

it('filters classes by bibhag', function () {
    $tenant = structureTenant();
    $user = structureAdmin($tenant);
    tenancy()->initialize($tenant);

    $hifz = Marhala::where('code', 'hifz')->firstOrFail();
    $nazera = Marhala::where('code', 'nazera')->firstOrFail();

    Jamaat::create(['marhala_id' => $hifz->id, 'name' => '১০ পারা']);
    Jamaat::create(['marhala_id' => $nazera->id, 'name' => 'নাযেরা প্রথম']);

    Livewire::actingAs($user)
        ->test(JamaatList::class)
        ->set('filterMarhala', (string) $hifz->id)
        ->assertSee('১০ পারা')
        ->assertDontSee('নাযেরা প্রথম');
});

// ------------------------------------------------------------ পৃথকীকরণ ----

it('never shows another madrasa\'s classes', function () {
    $a = structureTenant('madrasa-a');
    $b = structureTenant('madrasa-b');

    tenancy()->initialize($a);
    $marhala = Marhala::where('code', 'hifz')->firstOrFail();
    Jamaat::create(['marhala_id' => $marhala->id, 'name' => 'ক-মাদরাসার ক্লাস']);
    tenancy()->end();

    $this->actingAs(structureAdmin($b))
        ->get('http://madrasa-b.localhost/panel/academic/jamaats')
        ->assertOk()
        ->assertDontSee('ক-মাদরাসার ক্লাস');
});
