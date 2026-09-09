<?php

declare(strict_types=1);

use App\Livewire\Tenant\Cms\SiteImageList;
use App\Models\Central\Tenant;
use App\Models\Cms\SiteImage;
use App\Models\User;
use App\Services\Tenancy\TenantProvisioner;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

afterEach(fn () => tenancy()->end());

function storageTenant(): Tenant
{
    $tenant = app(TenantProvisioner::class)->provision(
        ['name' => 'দারুল উলুম মাদরাসা', 'slug' => 'darul-ulum', 'madrasa_type' => 'kitab'],
        'darul-ulum.localhost',
        ['name' => 'মুহতামিম', 'email' => 'admin@darul-ulum.test', 'password' => 'password'],
    );

    $tenant->update(['trial_ends_at' => now()->addMonth()]);

    return $tenant;
}

it('uploads a slide without falling back to the system temp dir', function () {
    $tenant = storageTenant();

    tenancy()->initialize($tenant);
    $admin = User::query()->where('tenant_id', $tenant->getKey())->firstOrFail();

    // এটিই ভেঙেছিল: framework/cache না থাকায় real-time facade লেখার
    // tempnam() সিস্টেম temp-এ পড়ত আর livewire/update ৫০০ দিত।
    $warnings = [];
    set_error_handler(function (int $no, string $message) use (&$warnings): bool {
        $warnings[] = $message;

        return true;
    }, E_WARNING | E_NOTICE);

    try {
        Livewire::actingAs($admin)
            ->test(SiteImageList::class, ['collection' => SiteImage::COLLECTION_SLIDER])
            ->set('image', UploadedFile::fake()->image('slide.png', 1200, 600))
            ->set('title', 'পরীক্ষামূলক স্লাইড')
            ->call('save')
            ->assertHasNoErrors();
    } finally {
        restore_error_handler();
    }

    expect($warnings)->not->toContain('tempnam(): file created in the system\'s temporary directory')
        ->and(SiteImage::query()->collection(SiteImage::COLLECTION_SLIDER)->count())->toBe(1);
});

it('gives every tenant its own framework cache directory', function () {
    $tenant = storageTenant();

    $root = storage_path('tenant'.$tenant->getTenantKey());

    expect($root.'/framework/cache')->toBeDirectory()
        ->and($root.'/framework/views')->toBeDirectory()
        ->and($root.'/framework/sessions')->toBeDirectory()
        ->and($root.'/app/public')->toBeDirectory();
});
