<?php

declare(strict_types=1);

use App\Livewire\Shared\TenantProbe;
use App\Models\Academic\AcademicSession;
use App\Models\Central\Tenant;
use Livewire\Livewire;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

/**
 * Plan risk #1: Livewire's own endpoints live outside routes/tenant.php. If
 * they are not wired through InitializeTenancyByDomain, every wire:click runs
 * without tenancy and the global scope silently returns the wrong rows.
 */
function probeTenant(string $slug, string $domain): Tenant
{
    $tenant = Tenant::create([
        'name' => $slug,
        'slug' => $slug,
        'status' => Tenant::STATUS_ACTIVE,
    ]);

    $tenant->domains()->create([
        'domain' => $domain,
        'is_primary' => true,
        'type' => 'subdomain',
    ]);

    return $tenant;
}

afterEach(fn () => tenancy()->end());

it('has tenant context inside a livewire action', function () {
    $a = probeTenant('madrasa-a', 'madrasa-a.localhost');
    $b = probeTenant('madrasa-b', 'madrasa-b.localhost');

    tenancy()->initialize($a);
    AcademicSession::create(['name' => 'সেশন-এ-১']);
    AcademicSession::create(['name' => 'সেশন-এ-২']);

    tenancy()->initialize($b);
    AcademicSession::create(['name' => 'সেশন-বি-১']);

    // Tenant A: sees exactly its own two sessions, on mount and after an action.
    tenancy()->initialize($a);
    Livewire::test(TenantProbe::class)
        ->assertSet('tenantName', 'madrasa-a')
        ->assertSet('sessionCount', 2)
        ->call('refreshFromTenant')
        ->assertSet('tenantName', 'madrasa-a')
        ->assertSet('sessionCount', 2);

    // Tenant B: sees only its own one session.
    tenancy()->initialize($b);
    Livewire::test(TenantProbe::class)
        ->assertSet('tenantName', 'madrasa-b')
        ->assertSet('sessionCount', 1)
        ->call('refreshFromTenant')
        ->assertSet('sessionCount', 1);
});

it('registers the livewire update route with tenancy middleware', function () {
    $middleware = collect(app('router')->getRoutes()->getRoutes())
        ->first(fn ($route) => $route->uri() === 'livewire/update')
        ->gatherMiddleware();

    expect($middleware)->toContain(InitializeTenancyByDomain::class);
});

it('registers the livewire upload route with tenancy middleware', function () {
    $middleware = collect(app('router')->getRoutes()->getRoutes())
        ->first(fn ($route) => $route->uri() === 'livewire/upload-file')
        ->gatherMiddleware();

    expect($middleware)->toContain(InitializeTenancyByDomain::class);
});
