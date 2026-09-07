<?php

declare(strict_types=1);

use App\Models\Academic\AcademicSession;
use App\Models\Central\Tenant;

/**
 * Tenant isolation is the single most important guarantee in a single-database
 * multi-tenant app: if the global scope ever fails, madrasa A sees madrasa B's
 * students. These tests must never be weakened.
 */
function makeTenant(string $slug): Tenant
{
    return Tenant::create([
        'name' => $slug,
        'slug' => $slug,
        'status' => Tenant::STATUS_ACTIVE,
    ]);
}

it('hides one tenant\'s rows from another tenant', function () {
    $a = makeTenant('madrasa-a');
    $b = makeTenant('madrasa-b');

    tenancy()->initialize($a);
    AcademicSession::create(['name' => 'সেশন-এ']);
    expect(AcademicSession::count())->toBe(1)
        ->and(AcademicSession::first()->name)->toBe('সেশন-এ');

    tenancy()->initialize($b);
    expect(AcademicSession::count())->toBe(0)
        ->and(AcademicSession::first())->toBeNull();

    AcademicSession::create(['name' => 'সেশন-বি']);
    expect(AcademicSession::count())->toBe(1)
        ->and(AcademicSession::first()->name)->toBe('সেশন-বি');

    tenancy()->end();
});

it('stamps tenant_id automatically on create', function () {
    $a = makeTenant('madrasa-a');

    tenancy()->initialize($a);
    $session = AcademicSession::create(['name' => 'সেশন']);

    expect($session->tenant_id)->toBe($a->id);

    tenancy()->end();
});

it('refuses to create tenant-scoped rows with no tenant context', function () {
    AcademicSession::create(['name' => 'অনাথ সেশন']);
})->throws(RuntimeException::class);

it('cannot fetch another tenant\'s row by primary key', function () {
    $a = makeTenant('madrasa-a');
    $b = makeTenant('madrasa-b');

    tenancy()->initialize($a);
    $sessionOfA = AcademicSession::create(['name' => 'সেশন-এ']);

    tenancy()->initialize($b);
    expect(AcademicSession::find($sessionOfA->id))->toBeNull();

    tenancy()->end();
});

it('cannot update or delete another tenant\'s row', function () {
    $a = makeTenant('madrasa-a');
    $b = makeTenant('madrasa-b');

    tenancy()->initialize($a);
    $sessionOfA = AcademicSession::create(['name' => 'সেশন-এ']);

    tenancy()->initialize($b);
    AcademicSession::where('id', $sessionOfA->id)->update(['name' => 'হ্যাক']);
    AcademicSession::where('id', $sessionOfA->id)->delete();

    tenancy()->initialize($a);
    $fresh = AcademicSession::find($sessionOfA->id);
    expect($fresh)->not->toBeNull()
        ->and($fresh->name)->toBe('সেশন-এ');

    tenancy()->end();
});

it('sees all tenants\' rows in the central context', function () {
    $a = makeTenant('madrasa-a');
    $b = makeTenant('madrasa-b');

    tenancy()->initialize($a);
    AcademicSession::create(['name' => 'সেশন-এ']);
    tenancy()->initialize($b);
    AcademicSession::create(['name' => 'সেশন-বি']);
    tenancy()->end();

    // Central context: super admin must be able to see across tenants.
    expect(AcademicSession::count())->toBe(2);
});
