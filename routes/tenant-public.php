<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant public site routes
|--------------------------------------------------------------------------
|
| প্রতিটি মাদরাসার নিজস্ব ল্যান্ডিং ওয়েবসাইট। এই ফাইল সবার শেষে
| রেজিস্টার হয়, কারণ এখানে wildcard page route আছে।
|
*/

Route::middleware('tenant.public')
    ->name('public.')
    ->group(function () {
        Route::get('/', fn () => view('public.home'))->name('home');
    });
