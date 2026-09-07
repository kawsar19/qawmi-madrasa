<?php

declare(strict_types=1);

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

Route::middleware('tenant.panel')
    ->prefix('panel')
    ->name('tenant.')
    ->group(function () {
        Route::get('/', fn () => view('tenant.dashboard'))->name('dashboard');
    });

// লগইন / লগআউট — প্যানেলের বাইরে, কারণ auth middleware লাগে না।
Route::middleware('tenant.guest')
    ->prefix('panel')
    ->name('tenant.')
    ->group(function () {
        Route::view('/login', 'tenant.auth.login')->name('login');
    });

require __DIR__.'/tenant-public.php';
