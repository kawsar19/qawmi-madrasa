<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Models\Central\Tenant;
use App\Models\User;

afterEach(fn () => tenancy()->end());

it('offers the tenant admin as a demo login on a madrasa domain', function () {
    $tenant = Tenant::factory()->withDomain()->create();
    $admin = User::factory()->forTenant($tenant)->create();

    tenancy()->initialize($tenant);

    Livewire::test(Login::class)
        ->assertSee('ডেমো লগইন')
        ->assertSee($admin->email);
});

it('offers the super admin as a demo login centrally', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    Livewire::test(Login::class)
        ->assertSee($superAdmin->email);
});

it('does not leak another madrasa\'s credentials', function () {
    $a = Tenant::factory()->withDomain('madrasa-a.localhost')->create();
    $b = Tenant::factory()->withDomain('madrasa-b.localhost')->create();

    $adminOfA = User::factory()->forTenant($a)->create();
    User::factory()->forTenant($b)->create();

    tenancy()->initialize($b);

    Livewire::test(Login::class)->assertDontSee($adminOfA->email);
});

it('NEVER shows demo credentials in production', function () {
    $tenant = Tenant::factory()->withDomain()->create();
    $admin = User::factory()->forTenant($tenant)->create();

    tenancy()->initialize($tenant);

    // The guard is on the environment, not a config flag, so it cannot be
    // switched on in production by accident.
    app()->detectEnvironment(fn () => 'production');

    Livewire::test(Login::class)
        ->assertDontSee('ডেমো লগইন')
        ->assertDontSee($admin->email);
});

it('fills the form from the demo box', function () {
    $tenant = Tenant::factory()->withDomain()->create();
    $admin = User::factory()->forTenant($tenant)->create();

    tenancy()->initialize($tenant);

    Livewire::test(Login::class)
        ->call('fillDemo', $admin->email, 'password')
        ->assertSet('email', $admin->email)
        ->assertSet('password', 'password')
        ->call('login')
        ->assertHasNoErrors();

    expect(auth()->check())->toBeTrue();
});
