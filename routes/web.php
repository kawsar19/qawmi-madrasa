<?php

declare(strict_types=1);

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
        Route::get('/', fn () => view('welcome'))->name('home');

        Route::prefix('admin')
            ->name('central.')
            ->middleware(['auth', 'super-admin'])
            ->group(function () {
                Route::get('/', fn () => view('central.dashboard'))->name('dashboard');
            });
    });
}
