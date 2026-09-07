<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\LogoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant panel routes  (/panel/*)
|--------------------------------------------------------------------------
|
| মাদরাসার লগইন-করা প্যানেল। Registered BEFORE routes/tenant-public.php,
| because the public site owns "/" and may use wildcard page routes —
| Laravel matches in registration order, so the panel must come first.
|
*/

Route::prefix('panel')->name('tenant.')->group(function () {
    // লগইন — tenancy লাগে, auth লাগে না।
    Route::middleware(['tenant.guest', 'guest'])->group(function () {
        Route::view('/login', 'tenant.auth.login')->name('login');
    });

    Route::middleware(['tenant.guest', 'auth'])->group(function () {
        Route::post('/logout', LogoutController::class)->name('logout');
    });

    Route::middleware('tenant.panel')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
    });
});

require __DIR__.'/tenant-public.php';
