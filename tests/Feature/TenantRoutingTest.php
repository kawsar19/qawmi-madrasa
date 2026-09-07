<?php

declare(strict_types=1);

use App\Models\Central\Tenant;

function tenantWithDomain(string $slug, string $domain): Tenant
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

it('serves each tenant its own public site by domain', function () {
    tenantWithDomain('madrasa-a', 'madrasa-a.localhost');
    tenantWithDomain('madrasa-b', 'madrasa-b.localhost');

    $this->get('http://madrasa-a.localhost/')
        ->assertOk()
        ->assertSee('madrasa-a');

    $this->get('http://madrasa-b.localhost/')
        ->assertOk()
        ->assertSee('madrasa-b');
});

it('serves the central site on a central domain', function () {
    $this->get('http://localhost/')
        ->assertOk()
        ->assertDontSee('মাদরাসার পাবলিক ওয়েবসাইট');
});

it('blocks the tenant panel on a central domain', function () {
    // PreventAccessFromCentralDomains must reject /panel on the central host.
    $this->get('http://localhost/panel')
        ->assertStatus(404);
});

it('returns 404 for an unknown domain', function () {
    $this->get('http://not-a-tenant.localhost/')
        ->assertStatus(404);
});

it('redirects a guest from the panel to that tenant\'s login page', function () {
    // Laravel's `auth` middleware redirects to a route named "login"; ours is
    // "tenant.login", so without redirectGuestsTo this 500s.
    tenantWithDomain('madrasa-a', 'madrasa-a.localhost');

    $this->get('http://madrasa-a.localhost/panel')
        ->assertRedirect('http://madrasa-a.localhost/panel/login');
});

it('serves the tenant login page', function () {
    tenantWithDomain('madrasa-a', 'madrasa-a.localhost');

    $this->get('http://madrasa-a.localhost/panel/login')
        ->assertOk()
        ->assertSee('madrasa-a');
});
