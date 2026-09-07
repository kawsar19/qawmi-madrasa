<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Models\Central\Tenant;
use App\Models\User;

/**
 * Tenant-scoped authentication.
 *
 * Users are unique per (tenant_id, email), so the same address can exist in
 * two madrasas. These tests prove a login only works on its own tenant's
 * domain — the failure they guard against is a user signing in to another
 * madrasa's panel.
 */
afterEach(fn () => tenancy()->end());

it('lets a madrasa user log in on their own domain', function () {
    $tenant = Tenant::factory()->withDomain('madrasa-a.localhost')->create();
    tenancy()->initialize($tenant);
    User::factory()->forTenant($tenant)->create(['email' => 'admin@example.com']);
    tenancy()->end();

    Livewire::withoutLazyLoading();

    $this->withServerVariables(['HTTP_HOST' => 'madrasa-a.localhost']);

    $this->get('http://madrasa-a.localhost/panel/login')->assertOk();

    tenancy()->initialize($tenant);

    Livewire::test(Login::class)
        ->set('email', 'admin@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertHasNoErrors();

    expect(auth()->check())->toBeTrue()
        ->and(auth()->user()->tenant_id)->toBe($tenant->id);
});

it('refuses a user from another madrasa', function () {
    $a = Tenant::factory()->withDomain('madrasa-a.localhost')->create();
    $b = Tenant::factory()->withDomain('madrasa-b.localhost')->create();

    // The account exists only in madrasa A.
    User::factory()->forTenant($a)->create(['email' => 'admin@example.com']);

    // Attempt the same credentials on madrasa B.
    tenancy()->initialize($b);

    Livewire::test(Login::class)
        ->set('email', 'admin@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertHasErrors('email');

    expect(auth()->check())->toBeFalse();
});

it('refuses a super admin on a tenant domain', function () {
    $tenant = Tenant::factory()->withDomain('madrasa-a.localhost')->create();
    User::factory()->superAdmin()->create(['email' => 'super@example.com']);

    tenancy()->initialize($tenant);

    Livewire::test(Login::class)
        ->set('email', 'super@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertHasErrors('email');

    expect(auth()->check())->toBeFalse();
});

it('refuses a madrasa user on the central domain', function () {
    $tenant = Tenant::factory()->withDomain('madrasa-a.localhost')->create();
    User::factory()->forTenant($tenant)->create(['email' => 'admin@example.com']);

    // No tenancy initialized => central context => super admins only.
    Livewire::test(Login::class)
        ->set('email', 'admin@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertHasErrors('email');

    expect(auth()->check())->toBeFalse();
});

it('lets a super admin log in centrally', function () {
    User::factory()->superAdmin()->create(['email' => 'super@example.com']);

    Livewire::test(Login::class)
        ->set('email', 'super@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertHasNoErrors();

    expect(auth()->check())->toBeTrue()
        ->and(auth()->user()->isSuperAdmin())->toBeTrue();
});

it('refuses an inactive user', function () {
    $tenant = Tenant::factory()->withDomain('madrasa-a.localhost')->create();
    User::factory()->forTenant($tenant)->inactive()->create(['email' => 'admin@example.com']);

    tenancy()->initialize($tenant);

    Livewire::test(Login::class)
        ->set('email', 'admin@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertHasErrors('email');

    expect(auth()->check())->toBeFalse();
});

it('refuses a wrong password', function () {
    $tenant = Tenant::factory()->withDomain('madrasa-a.localhost')->create();
    User::factory()->forTenant($tenant)->create(['email' => 'admin@example.com']);

    tenancy()->initialize($tenant);

    Livewire::test(Login::class)
        ->set('email', 'admin@example.com')
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertHasErrors('email');

    expect(auth()->check())->toBeFalse();
});
