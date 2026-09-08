<?php

declare(strict_types=1);

use App\Http\Middleware\InitializeTenancyIfTenantDomain;
use App\Livewire\Shared\TenantProbe;
use App\Models\Academic\AcademicSession;
use App\Models\Central\Tenant;
use Illuminate\Http\Request;
use Livewire\Livewire;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Symfony\Component\HttpFoundation\Response;

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

    expect($middleware)->toContain(InitializeTenancyIfTenantDomain::class);
});

it('registers the livewire upload route with tenancy middleware', function () {
    $middleware = collect(app('router')->getRoutes()->getRoutes())
        ->first(fn ($route) => $route->uri() === 'livewire/upload-file')
        ->gatherMiddleware();

    expect($middleware)->toContain(InitializeTenancyIfTenantDomain::class);
});

it('does not block livewire on the central domain', function () {
    // Regression: the update endpoint is shared by the tenant panel and the
    // super-admin panel. Guarding it with PreventAccessFromCentralDomains
    // 404'd every super-admin interaction, so the central panel was unusable.
    $middleware = collect(app('router')->getRoutes()->getRoutes())
        ->first(fn ($route) => $route->uri() === 'livewire/update')
        ->gatherMiddleware();

    expect($middleware)->not->toContain(PreventAccessFromCentralDomains::class);
});

it('leaves tenancy uninitialized for livewire on a central host', function () {
    $middleware = new InitializeTenancyIfTenantDomain(app(InitializeTenancyByDomain::class));

    $middleware->handle(Request::create('http://localhost/livewire/update'), function () {
        expect(tenancy()->initialized)->toBeFalse();

        return new Response;
    });
});

it('initializes tenancy for livewire on a tenant host', function () {
    $tenant = probeTenant('madrasa-a', 'madrasa-a.localhost');

    $middleware = new InitializeTenancyIfTenantDomain(app(InitializeTenancyByDomain::class));

    $middleware->handle(Request::create('http://madrasa-a.localhost/livewire/update'), function () use ($tenant) {
        expect(tenancy()->initialized)->toBeTrue()
            ->and(tenant()->getTenantKey())->toBe($tenant->id);

        return new Response;
    });
});
