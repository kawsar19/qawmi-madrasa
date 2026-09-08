<?php

declare(strict_types=1);

use App\Livewire\Central\Tenants\TenantCreate;
use App\Livewire\Central\Tenants\TenantList;
use App\Models\Academic\Kitab;
use App\Models\Academic\Marhala;
use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use App\Models\User;

afterEach(fn () => tenancy()->end());

function superAdmin(): User
{
    return User::factory()->superAdmin()->create();
}

it('keeps the admin panel away from anyone who is not a super admin', function () {
    $tenant = Tenant::factory()->withDomain()->create();

    // A madrasa user is authenticated, but must not reach the central panel.
    $this->actingAs(User::factory()->forTenant($tenant)->create())
        ->get('http://localhost/admin')
        ->assertForbidden();
});

it('redirects a guest to the central login', function () {
    // route('central.login') resolves to the first configured central domain,
    // so the redirect is absolute rather than host-relative.
    $this->get('http://localhost/admin')
        ->assertRedirect(route('central.login'));
});

it('shows the dashboard to a super admin', function () {
    Tenant::factory()->count(3)->create();
    Tenant::factory()->suspended()->create();

    $this->actingAs(superAdmin())
        ->get('http://localhost/admin')
        ->assertOk()
        ->assertSee('মোট মাদরাসা')
        ->assertSee('৪');   // 4 tenants, in Bengali numerals
});

it('lists tenants and filters by search and status', function () {
    Tenant::factory()->create(['name' => 'দারুল উলুম', 'slug' => 'darul-ulum']);
    Tenant::factory()->suspended()->create(['name' => 'রাহমানিয়া', 'slug' => 'rahmania']);

    $this->actingAs(superAdmin());

    Livewire::test(TenantList::class)
        ->assertSee('দারুল উলুম')
        ->assertSee('রাহমানিয়া')
        ->set('search', 'darul-ulum')
        ->assertSee('দারুল উলুম')
        ->assertDontSee('রাহমানিয়া')
        ->set('search', '')
        ->set('status', Tenant::STATUS_SUSPENDED)
        ->assertSee('রাহমানিয়া')
        ->assertDontSee('দারুল উলুম');
});

it('suspends and reactivates a tenant', function () {
    $tenant = Tenant::factory()->create();
    $this->actingAs(superAdmin());

    Livewire::test(TenantList::class)->call('suspend', $tenant->id);
    expect($tenant->fresh()->status)->toBe(Tenant::STATUS_SUSPENDED);

    Livewire::test(TenantList::class)->call('activate', $tenant->id);
    expect($tenant->fresh()->status)->toBe(Tenant::STATUS_ACTIVE);
});

it('provisions a complete tenant from the create form', function () {
    $plan = Plan::create([
        'name' => 'স্ট্যান্ডার্ড', 'slug' => 'standard', 'price' => 12000,
        'billing_cycle' => 'yearly', 'is_active' => true,
    ]);

    $this->actingAs(superAdmin());

    Livewire::test(TenantCreate::class)
        ->set('name', 'জামিয়া ইসলামিয়া')
        ->set('adminName', 'মুহতামিম')
        ->set('adminEmail', 'admin@jamia.test')
        ->set('adminPassword', 'password123')
        ->set('planId', $plan->id)
        ->set('subscriptionMonths', 12)
        ->call('save')
        ->assertHasNoErrors();

    $tenant = Tenant::where('slug', 'jamiya-islamiya')->firstOrFail();

    // Slug and domain are derived from the Bengali name, readably.
    expect($tenant->domains()->first()->domain)->toBe('jamiya-islamiya.app.localhost');

    // Fully provisioned, not just a row in `tenants`.
    tenancy()->initialize($tenant);
    expect(Marhala::count())->toBe(10)
        ->and(Kitab::count())->toBe(189)
        ->and(User::where('tenant_id', $tenant->id)->count())->toBe(1);
    tenancy()->end();

    expect(Subscription::where('tenant_id', $tenant->id)->exists())->toBeTrue();
});

it('rejects a duplicate slug or domain', function () {
    Tenant::factory()->withDomain('taken.app.localhost')->create(['slug' => 'taken']);

    $this->actingAs(superAdmin());

    Livewire::test(TenantCreate::class)
        ->set('name', 'যেকোনো নাম')
        ->set('slug', 'taken')
        ->set('domain', 'taken.app.localhost')
        ->set('adminName', 'মুহতামিম')
        ->set('adminEmail', 'admin@x.test')
        ->set('adminPassword', 'password123')
        ->call('save')
        ->assertHasErrors(['slug', 'domain']);
});

it('validates the bangladeshi mobile format', function () {
    $this->actingAs(superAdmin());

    Livewire::test(TenantCreate::class)
        ->set('name', 'কোনো মাদরাসা')
        ->set('adminName', 'মুহতামিম')
        ->set('adminEmail', 'admin@x.test')
        ->set('adminPassword', 'password123')
        ->set('adminMobile', '12345')
        ->call('save')
        ->assertHasErrors('adminMobile');
});
