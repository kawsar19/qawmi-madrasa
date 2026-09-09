<?php

declare(strict_types=1);

use App\Livewire\Tenant\Cms\NoticeList;
use App\Livewire\Tenant\Cms\SiteSettingForm;
use App\Models\Central\Tenant;
use App\Models\Cms\Notice;
use App\Models\Cms\SiteSetting;
use App\Models\User;
use App\Services\Tenancy\TenantProvisioner;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

afterEach(fn () => tenancy()->end());

function cmsTenant(string $slug = 'darul-ulum'): Tenant
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

function cmsAdmin(Tenant $tenant): User
{
    tenancy()->initialize($tenant);
    $user = User::query()->where('tenant_id', $tenant->getKey())->firstOrFail();
    tenancy()->end();

    return $user;
}

/** টেন্যান্ট কনটেক্সট সহ — Livewire::test() নিজে tenancy চালু করে না। */
function cmsActor(Tenant $tenant): User
{
    $user = cmsAdmin($tenant);
    tenancy()->initialize($tenant);

    return $user;
}

it('shows the website settings screen', function () {
    $tenant = cmsTenant();

    $this->actingAs(cmsAdmin($tenant))
        ->get('http://darul-ulum.localhost/panel/website/settings')
        ->assertOk()
        ->assertSee('ওয়েবসাইট সেটিংস')
        ->assertSee('টেমপ্লেট');
});

it('saves the template, colours and identity', function () {
    $tenant = cmsTenant();

    Livewire::actingAs(cmsActor($tenant))
        ->test(SiteSettingForm::class)
        ->set('template', 'classic')
        ->set('brandColor', '#14532D')
        ->set('accentColor', '#B45309')
        ->set('siteTitle', 'বেলাশী ফাযিল মাদরাসা')
        ->set('tagline', 'দ্বীনি শিক্ষার প্রতিষ্ঠান')
        ->set('phone', '01712345678')
        ->call('save')
        ->assertHasNoErrors();

    $settings = SiteSetting::current();

    expect($settings->template)->toBe('classic')
        // হেক্স ছোট হাতের অক্ষরে সংরক্ষিত হয়, যাতে তুলনা করা সহজ হয়।
        ->and($settings->brand_color)->toBe('#14532d')
        ->and($settings->accent_color)->toBe('#b45309')
        ->and($settings->site_title)->toBe('বেলাশী ফাযিল মাদরাসা')
        ->and($settings->phone)->toBe('01712345678');
});

it('rejects a colour that is not a hex value', function () {
    $tenant = cmsTenant();

    // রঙটি সরাসরি CSS ভেরিয়েবলে বসে, তাই যা খুশি লেখা ঢুকতে দেওয়া যাবে না।
    Livewire::actingAs(cmsActor($tenant))
        ->test(SiteSettingForm::class)
        ->set('brandColor', 'red; } body { display:none')
        ->call('save')
        ->assertHasErrors(['brandColor']);
});

it('rejects a template that is not installed', function () {
    $tenant = cmsTenant();

    Livewire::actingAs(cmsActor($tenant))
        ->test(SiteSettingForm::class)
        ->set('template', 'nonexistent')
        ->call('save')
        ->assertHasErrors(['template']);
});

it('offers only installed templates', function () {
    $tenant = cmsTenant();

    Livewire::actingAs(cmsActor($tenant))
        ->test(SiteSettingForm::class)
        ->assertViewHas('templates', fn (array $templates): bool => array_key_exists('classic', $templates));
});

it('changing the template changes what the public site renders', function () {
    $tenant = cmsTenant();

    Livewire::actingAs(cmsActor($tenant))
        ->test(SiteSettingForm::class)
        ->set('brandColor', '#7f1d1d')
        ->call('save')
        ->assertHasNoErrors();

    tenancy()->end();

    // প্যানেলে বদলালে সাইটে সাথে সাথেই দেখা যাবে।
    $this->get('http://darul-ulum.localhost/')
        ->assertOk()
        ->assertSee('--site-brand: #7f1d1d', false);
});

it('turning the site off makes it 404', function () {
    $tenant = cmsTenant();

    Livewire::actingAs(cmsActor($tenant))
        ->test(SiteSettingForm::class)
        ->set('isPublished', false)
        ->call('save')
        ->assertHasNoErrors();

    tenancy()->end();

    $this->get('http://darul-ulum.localhost/')->assertNotFound();
});

it('hiding a section removes its page too', function () {
    $tenant = cmsTenant();

    Livewire::actingAs(cmsActor($tenant))
        ->test(SiteSettingForm::class)
        ->set('showNotices', false)
        ->call('save')
        ->assertHasNoErrors();

    tenancy()->end();

    $this->get('http://darul-ulum.localhost/notice')->assertNotFound();
});

it('stores an uploaded logo', function () {
    Storage::fake('public');

    $tenant = cmsTenant();

    Livewire::actingAs(cmsActor($tenant))
        ->test(SiteSettingForm::class)
        ->set('logo', UploadedFile::fake()->image('logo.png'))
        ->call('save')
        ->assertHasNoErrors();

    $path = SiteSetting::current()->logo_path;

    expect($path)->not->toBeNull();
    Storage::disk('public')->assertExists($path);
});

it('keeps the old logo when saving without a new one', function () {
    Storage::fake('public');

    $tenant = cmsTenant();
    $user = cmsActor($tenant);

    Livewire::actingAs($user)
        ->test(SiteSettingForm::class)
        ->set('logo', UploadedFile::fake()->image('logo.png'))
        ->call('save');

    $original = SiteSetting::current()->logo_path;

    Livewire::actingAs($user)
        ->test(SiteSettingForm::class)
        ->set('tagline', 'নতুন ট্যাগলাইন')
        ->call('save')
        ->assertHasNoErrors();

    // ছবি না দিলে পুরনোটাই থাকবে — নইলে প্রতি সংরক্ষণে লোগো মুছে যেত।
    expect(SiteSetting::current()->logo_path)->toBe($original);
});

it('adds a notice that shows on the public site', function () {
    $tenant = cmsTenant();

    Livewire::actingAs(cmsActor($tenant))
        ->test(NoticeList::class)
        ->set('title', 'ভর্তি বিজ্ঞপ্তি')
        ->set('body', 'ভর্তি চলছে।')
        ->set('category', Notice::CATEGORY_ADMISSION)
        ->call('save')
        ->assertHasNoErrors();

    tenancy()->end();

    $this->get('http://darul-ulum.localhost/notice')
        ->assertOk()
        ->assertSee('ভর্তি বিজ্ঞপ্তি');
});

it('a draft notice stays off the public site', function () {
    $tenant = cmsTenant();

    Livewire::actingAs(cmsActor($tenant))
        ->test(NoticeList::class)
        ->set('title', 'গোপন খসড়া')
        ->set('isPublished', false)
        ->call('save')
        ->assertHasNoErrors();

    tenancy()->end();

    $this->get('http://darul-ulum.localhost/notice')
        ->assertOk()
        ->assertDontSee('গোপন খসড়া');
});

it('toggles a notice between published and draft', function () {
    $tenant = cmsTenant();
    $user = cmsActor($tenant);

    $notice = Notice::create([
        'title' => 'একটি নোটিশ',
        'is_published' => true,
        'published_on' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(NoticeList::class)
        ->call('togglePublished', $notice->id);

    expect($notice->refresh()->is_published)->toBeFalse();

    Livewire::actingAs($user)
        ->test(NoticeList::class)
        ->call('togglePublished', $notice->id);

    expect($notice->refresh()->is_published)->toBeTrue();
});

it('refuses an expiry date before the publish date', function () {
    $tenant = cmsTenant();

    // মেয়াদ প্রকাশের আগে হলে নোটিশটি কখনো দেখাই যেত না।
    Livewire::actingAs(cmsActor($tenant))
        ->test(NoticeList::class)
        ->set('title', 'ভুল তারিখ')
        ->set('publishedOn', now()->toDateString())
        ->set('expiresOn', now()->subWeek()->toDateString())
        ->call('save')
        ->assertHasErrors(['expiresOn']);
});

it('requires a notice title', function () {
    $tenant = cmsTenant();

    Livewire::actingAs(cmsActor($tenant))
        ->test(NoticeList::class)
        ->call('save')
        ->assertHasErrors(['title']);
});

it('never shows another madrasa\'s settings', function () {
    $a = cmsTenant('madrasa-a');

    Livewire::actingAs(cmsActor($a))
        ->test(SiteSettingForm::class)
        ->set('siteTitle', 'ক-মাদরাসার সাইট')
        ->call('save')
        ->assertHasNoErrors();

    tenancy()->end();

    // খ-মাদরাসা পরে তৈরি: প্রভিশনিং permission-team state বদলায়,
    // যা একই টেস্টে আগের Livewire কম্পোনেন্টকে বিভ্রান্ত করে।
    $b = cmsTenant('madrasa-b');

    // খ-মাদরাসার সেটিংসে ক-এর নাম কোনোভাবেই আসবে না।
    tenancy()->initialize($b);
    expect(SiteSetting::current()->site_title)->toBeNull();
    tenancy()->end();

    $this->actingAs(cmsAdmin($b))
        ->get('http://madrasa-b.localhost/panel/website/settings')
        ->assertOk()
        ->assertDontSee('ক-মাদরাসার সাইট');
});
