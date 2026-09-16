<?php

declare(strict_types=1);

use App\Models\Central\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * `php artisan admin:create`.
 *
 * DatabaseSeeder returns early on production, so this command is the only way
 * into a freshly migrated production database. The test that matters most is
 * that the password it stores actually authenticates: User casts `password`
 * to 'hashed', so hashing it in the command too would double-hash it and lock
 * everyone out — with the command still reporting success.
 */
it('creates a super admin who can authenticate', function () {
    $this->artisan('admin:create', [
        '--email' => 'super@example.test',
        '--name' => 'সুপার অ্যাডমিন',
        '--password' => 'Str0ngPass!2026',
    ])->assertSuccessful();

    $user = User::query()->where('email', 'super@example.test')->sole();

    expect($user->tenant_id)->toBeNull()
        ->and($user->is_active)->toBeTrue();

    expect(Auth::validate([
        'email' => 'super@example.test',
        'password' => 'Str0ngPass!2026',
    ]))->toBeTrue();
});

it('rejects a password shorter than eight characters', function () {
    $this->artisan('admin:create', [
        '--email' => 'weak@example.test',
        '--name' => 'X',
        '--password' => 'short',
    ])->assertFailed();

    expect(User::query()->where('email', 'weak@example.test')->exists())->toBeFalse();
});

it('rejects an email another super admin already uses', function () {
    User::factory()->create(['tenant_id' => null, 'email' => 'taken@example.test']);

    $this->artisan('admin:create', [
        '--email' => 'taken@example.test',
        '--name' => 'X',
        '--password' => 'Str0ngPass!2026',
    ])->assertFailed();

    expect(User::query()->where('email', 'taken@example.test')->count())->toBe(1);
});

/**
 * The unique index is on (tenant_id, email), so a madrasa admin and a super
 * admin may share an address. An unscoped unique rule would wrongly reject it.
 */
it('allows an email that a madrasa user already uses', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);
    User::factory()->forTenant($tenant)->create(['email' => 'shared@example.test']);
    tenancy()->end();

    $this->artisan('admin:create', [
        '--email' => 'shared@example.test',
        '--name' => 'সুপার অ্যাডমিন',
        '--password' => 'Str0ngPass!2026',
    ])->assertSuccessful();

    expect(User::query()->where('email', 'shared@example.test')->whereNull('tenant_id')->exists())
        ->toBeTrue();
});

/**
 * Hosts without a shell run this from the boot script on every deploy, so a
 * redeploy must not fail on the account it created last time.
 */
it('is idempotent with --if-missing', function () {
    $args = [
        '--email' => 'boot@example.test',
        '--name' => 'সুপার অ্যাডমিন',
        '--password' => 'Str0ngPass!2026',
        '--if-missing' => true,
    ];

    $this->artisan('admin:create', $args)->assertSuccessful();
    $this->artisan('admin:create', $args)->assertSuccessful();

    expect(User::query()->where('email', 'boot@example.test')->count())->toBe(1);
});

it('still fails on a duplicate email without --if-missing', function () {
    User::factory()->create(['tenant_id' => null, 'email' => 'boot2@example.test']);

    $this->artisan('admin:create', [
        '--email' => 'boot2@example.test',
        '--name' => 'X',
        '--password' => 'Str0ngPass!2026',
    ])->assertFailed();
});
