<?php

declare(strict_types=1);

use App\Models\Central\Plan;
use App\Models\Central\Tenant;

afterEach(fn () => tenancy()->end());

it('shows the marketing landing page on the central domain', function () {
    $this->get('http://localhost/')
        ->assertOk()
        ->assertSee('মাদরাসার সব হিসাব', escape: false)
        ->assertSee('প্যাকেজ ও মূল্য', escape: false);
});

it('lists active plans with prices in Bengali digits', function () {
    Plan::create([
        'name' => 'পরীক্ষামূলক',
        'slug' => 'test-plan',
        'price' => 12000,
        'billing_cycle' => 'yearly',
        'max_students' => 500,
        'features' => ['finance', 'hifz'],
        'is_active' => true,
        'sort_order' => 90,
    ]);

    $this->get('http://localhost/')
        ->assertOk()
        ->assertSee('পরীক্ষামূলক', escape: false)
        ->assertSee('৳ ১২,০০০', escape: false)      // Bn::taka, not a raw 12000
        ->assertSee('৫০০ জন ছাত্র পর্যন্ত', escape: false)
        ->assertSee('ফি ও হিসাব', escape: false)     // feature slug → Bengali label
        ->assertDontSee('>finance<', escape: false); // raw slug must not leak
});

it('hides inactive plans', function () {
    Plan::create([
        'name' => 'গোপন প্যাকেজ',
        'slug' => 'hidden-plan',
        'price' => 999,
        'billing_cycle' => 'yearly',
        'is_active' => false,
        'sort_order' => 91,
    ]);

    $this->get('http://localhost/')
        ->assertOk()
        ->assertDontSee('গোপন প্যাকেজ', escape: false);
});

it('describes an unlimited plan without printing a null limit', function () {
    Plan::create([
        'name' => 'সীমাহীন',
        'slug' => 'unlimited-plan',
        'price' => 50000,
        'billing_cycle' => 'yearly',
        'max_students' => null,
        'max_teachers' => null,
        'is_active' => true,
        'sort_order' => 92,
    ]);

    $this->get('http://localhost/')
        ->assertOk()
        ->assertSee('ছাত্রসংখ্যায় কোনো সীমা নেই', escape: false);
});

it('does not replace a tenant public site with the landing page', function () {
    // Central "/" and tenant "/" share a method + URI, so a regression here
    // would silently serve the marketing page to every madrasa.
    $tenant = Tenant::factory()->withDomain()->create();
    $domain = $tenant->domains()->first()->domain;

    $this->get('http://'.$domain.'/')
        ->assertOk()
        ->assertDontSee('প্যাকেজ ও মূল্য', escape: false);
});
