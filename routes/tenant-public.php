<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\PublicSiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant public site routes
|--------------------------------------------------------------------------
|
| প্রতিটি মাদরাসার নিজস্ব ল্যান্ডিং ওয়েবসাইট। এই ফাইল সবার শেষে
| রেজিস্টার হয়, কারণ এখানে wildcard page route আছে।
|
| রুটগুলো টেমপ্লেট-নিরপেক্ষ: কোন টেমপ্লেটের ভিউ দেখানো হবে তা
| SiteTemplate ঠিক করে, তাই নতুন টেমপ্লেট যোগ করতে এই ফাইল ছুঁতে হয় না।
|
*/

Route::middleware('tenant.public')
    ->name('public.')
    ->group(function () {
        Route::get('/', [PublicSiteController::class, 'home'])->name('home');
        Route::get('/porichiti', [PublicSiteController::class, 'about'])->name('about');
        Route::get('/notice', [PublicSiteController::class, 'notices'])->name('notices');
        Route::get('/notice/{id}', [PublicSiteController::class, 'notice'])
            ->whereNumber('id')
            ->name('notice');
        Route::get('/shikkhok', [PublicSiteController::class, 'teachers'])->name('teachers');
        Route::get('/jogajog', [PublicSiteController::class, 'contact'])->name('contact');
    });
