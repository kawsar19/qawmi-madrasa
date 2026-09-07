<?php

declare(strict_types=1);

use App\Models\Academic\AcademicSession;
use App\Models\Central\Tenant;
use App\Models\User;

afterEach(fn () => tenancy()->end());

function panelTenant(string $domain = 'madrasa-a.localhost'): Tenant
{
    return Tenant::factory()->withDomain($domain)->onTrial()->create();
}

it('shows the dashboard to a logged-in madrasa user', function () {
    $tenant = panelTenant();
    $user = User::factory()->forTenant($tenant)->create();

    $this->actingAs($user)
        ->get('http://madrasa-a.localhost/panel')
        ->assertOk()
        ->assertSee($tenant->name)
        ->assertSee('ড্যাশবোর্ড');
});

it('warns when no current academic session is set', function () {
    $tenant = panelTenant();
    $user = User::factory()->forTenant($tenant)->create();

    $this->actingAs($user)
        ->get('http://madrasa-a.localhost/panel')
        ->assertSee('কোনো চলতি শিক্ষাবর্ষ নির্ধারণ করা হয়নি।');
});

it('shows the current academic session when one exists', function () {
    $tenant = panelTenant();
    $user = User::factory()->forTenant($tenant)->create();

    tenancy()->initialize($tenant);
    AcademicSession::create(['name' => '১৪৪৬-১৪৪৭ হিজরি', 'is_current' => true]);
    tenancy()->end();

    $this->actingAs($user)
        ->get('http://madrasa-a.localhost/panel')
        ->assertOk()
        ->assertSee('১৪৪৬-১৪৪৭ হিজরি')
        ->assertDontSee('কোনো চলতি শিক্ষাবর্ষ নির্ধারণ করা হয়নি।');
});

it('blocks a user from another madrasa', function () {
    panelTenant('madrasa-a.localhost');
    $b = Tenant::factory()->withDomain('madrasa-b.localhost')->onTrial()->create();

    // A user belonging to madrasa B must not reach madrasa A's panel, even
    // while authenticated.
    $userOfB = User::factory()->forTenant($b)->create();

    $this->actingAs($userOfB)
        ->get('http://madrasa-a.localhost/panel')
        ->assertForbidden();
});

it('blocks a tenant whose subscription has expired', function () {
    // No trial and no subscription => panel is locked.
    $tenant = Tenant::factory()->withDomain('madrasa-a.localhost')->create();
    $user = User::factory()->forTenant($tenant)->create();

    $this->actingAs($user)
        ->get('http://madrasa-a.localhost/panel')
        ->assertForbidden()
        ->assertSee('মেয়াদ শেষ');
});

it('blocks a suspended tenant', function () {
    $tenant = Tenant::factory()->withDomain('madrasa-a.localhost')->suspended()->onTrial()->create();
    $user = User::factory()->forTenant($tenant)->create();

    $this->actingAs($user)
        ->get('http://madrasa-a.localhost/panel')
        ->assertForbidden()
        ->assertSee('স্থগিত');
});
