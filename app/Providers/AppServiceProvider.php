<?php

declare(strict_types=1);

namespace App\Providers;

use App\Auth\TenantUserProvider;
use App\Services\Academic\CurrentSession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // চলতি শিক্ষাবর্ষ পুরো রিকোয়েস্ট জুড়ে একটাই instance।
        $this->app->scoped(CurrentSession::class);
    }

    public function boot(): void
    {
        $this->configureAuth();
        $this->configureModels();
        $this->configureLivewireTenancy();
        $this->registerBladeDirectives();
    }

    /**
     * Tenant-scoped ইউজার লুকআপ রেজিস্টার করে।
     */
    private function configureAuth(): void
    {
        Auth::provider('tenant-eloquent', function ($app, array $config) {
            return new TenantUserProvider($app['hash'], $config['model']);
        });
    }

    private function configureModels(): void
    {
        Model::shouldBeStrict($this->app->isLocal());
        Model::unguard();
    }

    /**
     * Livewire-এর নিজস্ব রুটগুলোকে tenant-aware করা।
     *
     * THIS IS NOT OPTIONAL. Livewire's update/script/upload endpoints are
     * registered by the package itself, outside routes/tenant.php, so by
     * default they run with NO tenant context. Every Livewire action would
     * then execute with tenancy uninitialized — the global scope becomes a
     * no-op and components silently read/write across madrasas.
     */
    private function configureLivewireTenancy(): void
    {
        $tenantMiddleware = [
            'web',
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
        ];

        Livewire::setUpdateRoute(
            fn ($handle) => Route::post('/livewire/update', $handle)
                ->middleware($tenantMiddleware)
        );

        Livewire::setScriptRoute(
            fn ($handle) => Route::get('/livewire/livewire.js', $handle)
        );
    }

    private function registerBladeDirectives(): void
    {
        // @bn(1234) → ১২৩৪
        Blade::directive('bn', fn (string $expr) => "<?php echo \App\Support\Bn::num($expr); ?>");

        // @taka(1234.5) → ৳ ১,২৩৪.৫০
        Blade::directive('taka', fn (string $expr) => "<?php echo \App\Support\Bn::taka($expr); ?>");

        // @hijri($date) → ২৫ রবিউল আউয়াল ১৪৪৮ হিজরি
        Blade::directive('hijri', fn (string $expr) => "<?php echo \App\Support\HijriDate::format($expr); ?>");
    }
}
