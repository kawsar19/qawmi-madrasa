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
*/

foreach (config('tenancy.central_domains') as $centralDomain) {
    Route::domain($centralDomain)->group(function () {
        Route::get('/', LandingController::class)->name('home');

        Route::middleware('guest')->group(function () {
            Route::view('/login', 'central.auth.login')->name('central.login');
        });

        Route::post('/logout', LogoutController::class)
            ->middleware('auth')
            ->name('central.logout');

        Route::prefix('admin')
            ->name('central.')
            ->middleware(['auth', 'super-admin'])
            ->group(function () {
                Route::get('/', DashboardController::class)->name('dashboard');

                Route::prefix('tenants')->name('tenants.')->group(function () {
                    Route::view('/', 'central.tenants.index')->name('index');
                    Route::view('/create', 'central.tenants.create')->name('create');
                    Route::get('/{tenant}/edit', fn (Tenant $tenant) => view('central.tenants.edit', ['tenant' => $tenant]))
                        ->name('edit');
                });

                Route::view('/plans', 'central.plans.index')->name('plans.index');
                Route::view('/subscriptions', 'central.subscriptions.index')->name('subscriptions.index');
            });
    });
}
