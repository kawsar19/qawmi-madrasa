<?php

declare(strict_types=1);

use App\Models\Academic\Kitab;
use App\Models\Academic\Marhala;
use App\Models\Central\Tenant;
use App\Models\Exam\GradeScale;
use App\Models\Finance\DonationKhat;
use App\Models\Finance\FeeHead;
use App\Models\Finance\LedgerAccount;
use App\Models\User;
use App\Services\Tenancy\TenantProvisioner;

afterEach(fn () => tenancy()->end());

function provision(string $slug = 'darul-ulum', string $preset = 'kitab_madrasa'): Tenant
{
    return app(TenantProvisioner::class)->provision(
        [
            'name' => 'দারুল উলুম মাদরাসা',
            'slug' => $slug,
            'madrasa_type' => 'kitab',
        ],
        "{$slug}.localhost",
        [
            'name' => 'মুহতামিম সাহেব',
            'email' => 'admin@example.com',
            'password' => 'password',
            'mobile' => '01712345678',
        ],
        $preset,
    );
}

it('provisions a tenant with domain, reference data and an admin user', function () {
    $tenant = provision();

    expect($tenant->exists)->toBeTrue()
        ->and($tenant->status)->toBe(Tenant::STATUS_ACTIVE)
        ->and($tenant->domains()->count())->toBe(1)
        ->and($tenant->domains()->first()->domain)->toBe('darul-ulum.localhost');

    tenancy()->initialize($tenant);

    expect(Marhala::count())->toBe(10)
        ->and(Kitab::count())->toBe(189)
        ->and(GradeScale::count())->toBe(5)
        ->and(FeeHead::count())->toBe(8)
        ->and(DonationKhat::count())->toBe(9)
        ->and(LedgerAccount::count())->toBe(19);
});

it('links every kitab to its marhala', function () {
    $tenant = provision();
    tenancy()->initialize($tenant);

    expect(Kitab::whereNull('marhala_id')->count())->toBe(0);

    $takmil = Marhala::where('code', 'takmil')->firstOrFail();
    expect(Kitab::where('marhala_id', $takmil->id)->count())->toBeGreaterThan(0);
});

it('creates an admin user who can log in and holds the admin role', function () {
    $tenant = provision();
    tenancy()->initialize($tenant);

    $user = User::where('email', 'admin@example.com')->firstOrFail();

    expect($user->tenant_id)->toBe($tenant->id)
        ->and($user->isSuperAdmin())->toBeFalse()
        ->and(Hash::check('password', $user->password))->toBeTrue()
        ->and($user->hasRole('madrasa_admin'))->toBeTrue()
        ->and($user->can('people.student.create'))->toBeTrue();
});

it('seeds only hifz-track marhalas for a hifz madrasa preset', function () {
    $tenant = provision('hifzul-quran', 'hifz_madrasa');
    tenancy()->initialize($tenant);

    expect(Marhala::count())->toBe(3)
        ->and(Marhala::pluck('track')->unique()->sort()->values()->all())
        ->toBe(['hifz', 'nazera', 'qirat']);

    // Kitabs whose marhala was filtered out must not be copied in.
    expect(Kitab::count())->toBeGreaterThan(0)
        ->and(Kitab::whereNull('marhala_id')->count())->toBe(0);
});

it('keeps reference data separate per tenant', function () {
    $a = provision('madrasa-a');
    $b = provision('madrasa-b');

    tenancy()->initialize($a);
    Marhala::where('code', 'takmil')->update(['name' => 'পরিবর্তিত নাম']);

    // Editing A's copy must not touch B's.
    tenancy()->initialize($b);
    expect(Marhala::where('code', 'takmil')->first()->name)
        ->toBe('তাকমিল (দাওরায়ে হাদীস)');
});

it('creates the tenant storage tree so the first request does not 500', function () {
    $tenant = app(TenantProvisioner::class)->provision(
        ['name' => 'নতুন মাদরাসা', 'slug' => 'notun', 'madrasa_type' => 'kitab'],
        'notun.localhost',
        ['name' => 'মুহতামিম', 'email' => 'admin@notun.test', 'password' => 'password'],
    );

    // framework/cache না থাকলে প্রথম real-time facade লেখাই tempnam()
    // ওয়ার্নিং তুলে livewire/update-এ ৫০০ দেয়।
    $root = storage_path('tenant'.$tenant->getTenantKey());

    expect($root.'/app/public')->toBeDirectory()
        ->and($root.'/framework/cache')->toBeDirectory()
        ->and($root.'/framework/views')->toBeDirectory();
});
