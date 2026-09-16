<?php

declare(strict_types=1);

use App\Http\Controllers\Central\DashboardController;
use App\Http\Controllers\Central\LandingController;
use App\Http\Controllers\Central\LogoutController;
use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central routes
|--------------------------------------------------------------------------
|
| SaaS marketing site + সুপার অ্যাডমিন প্যানেল।
|
| Every central route is bound to an explicit central domain. Without this,
| Laravel would treat central "/" and tenant "/" as the same route (identical
| method + URI) and the one registered last would silently replace the other —
| routes/tenant.php is registered later, on app boot.
|
| The route *names* are attached on the first pass only. A name may be
| serialized once, so naming every pass makes `php artisan route:cache` abort
| with "Another route has already been assigned name [home]" the moment a
| second central domain exists — which is what broke the first Render deploy.
| The later passes still register working, reachable routes; they just cannot
| be the target of route('home'), and route() returns the first domain's URL.
|
*/

foreach (config('tenancy.central_domains') as $i => $centralDomain) {
    // Only the first pass names its routes; see the note above.
    $name = fn (string $n): ?string => $i === 0 ? $n : null;

    Route::domain($centralDomain)->group(function () use ($name) {
        Route::get('/', LandingController::class)->name($name('home'));

        Route::middleware('guest')->group(function () use ($name) {
            Route::view('/login', 'central.auth.login')->name($name('central.login'));
        });

        Route::post('/logout', LogoutController::class)
            ->middleware('auth')
            ->name($name('central.logout'));

        Route::prefix('admin')
            ->name($name('central.'))
            ->middleware(['auth', 'super-admin'])
            ->group(function () use ($name) {
                Route::get('/', DashboardController::class)->name($name('dashboard'));

                Route::prefix('tenants')->name($name('tenants.'))->group(function () use ($name) {
                    Route::view('/', 'central.tenants.index')->name($name('index'));
                    Route::view('/create', 'central.tenants.create')->name($name('create'));
                    Route::get('/{tenant}/edit', fn (Tenant $tenant) => view('central.tenants.edit', ['tenant' => $tenant]))
                        ->name($name('edit'));
                });

                Route::view('/plans', 'central.plans.index')->name($name('plans.index'));
                Route::view('/subscriptions', 'central.subscriptions.index')->name($name('subscriptions.index'));
            });
    });
}
