<?php

declare(strict_types=1);

use App\Models\Central\Tenant;
use App\Models\Cms\Notice;
use App\Models\Cms\SiteSetting;
use App\Services\People\EmployeeRegistrar;
use App\Services\Tenancy\TenantProvisioner;
use App\Support\SiteTemplate;

afterEach(fn () => tenancy()->end());

function siteTenant(string $slug = 'darul-ulum'): Tenant
{
    $tenant = app(TenantProvisioner::class)->provision(
        ['name' => 'দারুল উলুম মাদরাসা', 'slug' => $slug, 'madrasa_type' => 'kitab'],
        "{$slug}.localhost",
        [
            'name' => 'মুহতামিম সাহেব',
            'email' => "admin@{$slug}.test",
            'password' => 'password',
            'mobile' => '01712345678',
        ],
    );

    $tenant->update(['trial_ends_at' => now()->addMonth()]);

    return $tenant;
}

it('renders the public site without any settings row', function () {
    siteTenant();

    // একটি মাদরাসা কখনো সেটিংস স্ক্রিন না খুললেও সাইট ভাঙা চলবে না।
    $this->get('http://darul-ulum.localhost/')
        ->assertOk()
        ->assertSee('দারুল উলুম মাদরাসা');
});

it('shows the configured title, tagline and contact details', function () {
    $tenant = siteTenant();

    tenancy()->initialize($tenant);
    SiteSetting::updateOrCreate([], [
        'site_title' => 'বেলাশী ফাযিল মাদরাসা',
        'tagline' => 'দ্বীনি শিক্ষার নির্ভরযোগ্য প্রতিষ্ঠান',
        'phone' => '01712345678',
        'address' => 'কাপাসিয়া, গাজীপুর',
    ]);
    tenancy()->end();

    $this->get('http://darul-ulum.localhost/')
        ->assertOk()
        ->assertSee('বেলাশী ফাযিল মাদরাসা')
        ->assertSee('দ্বীনি শিক্ষার নির্ভরযোগ্য প্রতিষ্ঠান')
        ->assertSee('কাপাসিয়া, গাজীপুর');
});

it('paints the brand colour as a css variable', function () {
    $tenant = siteTenant();

    tenancy()->initialize($tenant);
    SiteSetting::updateOrCreate([], ['brand_color' => '#14532d']);
    tenancy()->end();

    // রঙ CSS ভেরিয়েবল হয়ে আসে, তাই প্রতি মাদরাসার জন্য আলাদা CSS
    // বিল্ড লাগে না — একটাই বান্ডিল সবার কাজে লাগে।
    $this->get('http://darul-ulum.localhost/')
        ->assertOk()
        ->assertSee('--site-brand: #14532d', false);
});

it('shows published notices and hides unpublished ones', function () {
    $tenant = siteTenant();

    tenancy()->initialize($tenant);
    Notice::create(['title' => 'ভর্তি চলছে', 'is_published' => true, 'published_on' => now()]);
    Notice::create(['title' => 'গোপন খসড়া', 'is_published' => false, 'published_on' => now()]);
    tenancy()->end();

    $this->get('http://darul-ulum.localhost/notice')
        ->assertOk()
        ->assertSee('ভর্তি চলছে')
        ->assertDontSee('গোপন খসড়া');
});

it('hides notices whose date has passed', function () {
    $tenant = siteTenant();

    tenancy()->initialize($tenant);
    Notice::create([
        'title' => 'মেয়াদোত্তীর্ণ নোটিশ',
        'is_published' => true,
        'published_on' => now()->subMonth(),
        'expires_on' => now()->subDay(),
    ]);
    tenancy()->end();

    $this->get('http://darul-ulum.localhost/notice')
        ->assertOk()
        ->assertDontSee('মেয়াদোত্তীর্ণ নোটিশ');
});

it('lists teachers but not non-teaching staff', function () {
    $tenant = siteTenant();

    tenancy()->initialize($tenant);
    $registrar = app(EmployeeRegistrar::class);
    $registrar->register(['name' => 'মাওলানা উস্তাদ', 'type' => 'teacher']);
    $registrar->register(['name' => 'বাবুর্চি সাহেব', 'type' => 'staff']);
    tenancy()->end();

    // শিক্ষকমণ্ডলীতে বাবুর্চি বা দারোয়ান আসা উচিত নয়।
    $this->get('http://darul-ulum.localhost/shikkhok')
        ->assertOk()
        ->assertSee('মাওলানা উস্তাদ')
        ->assertDontSee('বাবুর্চি সাহেব');
});

it('hides a section when the setting is turned off', function () {
    $tenant = siteTenant();

    tenancy()->initialize($tenant);
    Notice::create(['title' => 'একটি নোটিশ', 'is_published' => true, 'published_on' => now()]);
    SiteSetting::updateOrCreate([], ['show_notices' => false]);
    tenancy()->end();

    // সেকশন বন্ধ থাকলে হোমপেজে নেই, আর তার পেজটিও ৪০৪।
    $this->get('http://darul-ulum.localhost/')
        ->assertOk()
        ->assertDontSee('একটি নোটিশ');

    $this->get('http://darul-ulum.localhost/notice')->assertNotFound();
});

it('returns 404 while the site is unpublished', function () {
    $tenant = siteTenant();

    tenancy()->initialize($tenant);
    SiteSetting::updateOrCreate([], ['is_published' => false]);
    tenancy()->end();

    // সাইট বন্ধ রেখে কাজ করার সুযোগ — দর্শক ৪০৪ পাবেন, ভাঙা পেজ নয়।
    $this->get('http://darul-ulum.localhost/')->assertNotFound();
});

it('falls back to the classic template when the name is unknown', function () {
    $tenant = siteTenant();

    tenancy()->initialize($tenant);
    SiteSetting::updateOrCreate([], ['template' => 'nonexistent-template']);
    tenancy()->end();

    // অচেনা টেমপ্লেটে সাদা পর্দা দেখানো চলবে না।
    $this->get('http://darul-ulum.localhost/')
        ->assertOk()
        ->assertSee('দারুল উলুম মাদরাসা');
});

it('never shows another madrasa\'s notices', function () {
    $a = siteTenant('madrasa-a');
    siteTenant('madrasa-b');

    tenancy()->initialize($a);
    Notice::create(['title' => 'ক-মাদরাসার নোটিশ', 'is_published' => true, 'published_on' => now()]);
    tenancy()->end();

    $this->get('http://madrasa-b.localhost/notice')
        ->assertOk()
        ->assertDontSee('ক-মাদরাসার নোটিশ');
});

it('keeps the panel unreachable from the public site route', function () {
    siteTenant();

    // পাবলিক সাইট লগইন ছাড়াই খোলে; প্যানেল খোলে না।
    $this->get('http://darul-ulum.localhost/')->assertOk();
    $this->get('http://darul-ulum.localhost/panel')->assertRedirect();
});

it('serves every public page', function () {
    $tenant = siteTenant();

    tenancy()->initialize($tenant);
    $notice = Notice::create([
        'title' => 'একটি নোটিশ',
        'is_published' => true,
        'published_on' => now(),
    ]);
    tenancy()->end();

    foreach ([
        '/',
        '/porichiti',
        '/notice',
        "/notice/{$notice->id}",
        '/shikkhok',
        '/jogajog',
    ] as $path) {
        $this->get('http://darul-ulum.localhost'.$path)
            ->assertOk();
    }
});

it('gives each page its own title', function () {
    siteTenant();

    // আলাদা টাইটেল ছাড়া ব্রাউজার ট্যাব ও ফেসবুক শেয়ারে সব পেজ একরকম দেখাত।
    $this->get('http://darul-ulum.localhost/notice')
        ->assertOk()
        ->assertSee('<title>নোটিশ — দারুল উলুম মাদরাসা</title>', false);

    $this->get('http://darul-ulum.localhost/shikkhok')
        ->assertOk()
        ->assertSee('<title>শিক্ষকমণ্ডলী — দারুল উলুম মাদরাসা</title>', false);
});

it('hides a draft notice even when its url is known', function () {
    $tenant = siteTenant();

    tenancy()->initialize($tenant);
    $draft = Notice::create(['title' => 'গোপন খসড়া', 'is_published' => false]);
    tenancy()->end();

    // তালিকা থেকে লুকানো যথেষ্ট নয় — সরাসরি URL দিয়েও পড়া যাবে না।
    $this->get("http://darul-ulum.localhost/notice/{$draft->id}")->assertNotFound();
});

it('filters notices by category', function () {
    $tenant = siteTenant();

    tenancy()->initialize($tenant);
    Notice::create(['title' => 'ভর্তির খবর', 'category' => 'admission', 'is_published' => true, 'published_on' => now()]);
    Notice::create(['title' => 'ছুটির খবর', 'category' => 'holiday', 'is_published' => true, 'published_on' => now()]);
    tenancy()->end();

    $this->get('http://darul-ulum.localhost/notice?category=admission')
        ->assertOk()
        ->assertSee('ভর্তির খবর')
        ->assertDontSee('ছুটির খবর');
});

it('discovers templates from the filesystem', function () {
    // একটি নতুন টেমপ্লেট = শুধু ফোল্ডার; PHP বদলানো লাগে না।
    expect(SiteTemplate::installed())->toContain('classic')
        ->and(SiteTemplate::available())->toHaveKey('classic');
});

it('falls back per page when a template lacks one', function () {
    $tenant = siteTenant();
    $settings = null;

    tenancy()->initialize($tenant);
    $settings = SiteSetting::current();
    $settings->update(['template' => 'nonexistent']);

    // অসম্পূর্ণ টেমপ্লেটেও প্রতিটি পেজ classic থেকে এসে কাজ করে।
    foreach (SiteTemplate::PAGES as $page) {
        expect(view()->exists(SiteTemplate::view($settings, $page)))->toBeTrue();
    }

    tenancy()->end();
});
