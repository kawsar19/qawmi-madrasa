<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\AdmissionFormPdfController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\LogoutController;
use App\Http\Controllers\Tenant\ReceiptPdfController;
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

        Route::prefix('academic')->name('academic.')->group(function () {
            Route::view('/sessions', 'tenant.academic.sessions')
                ->middleware('can:academic.session.view')
                ->name('sessions');

            Route::view('/marhalas', 'tenant.academic.marhalas')
                ->middleware('can:academic.marhala.view')
                ->name('marhalas');

            Route::view('/jamaats', 'tenant.academic.jamaats')
                ->middleware('can:academic.jamaat.view')
                ->name('jamaats');
        });

        Route::prefix('website')->name('cms.')->group(function () {
            Route::view('/settings', 'tenant.cms.site-settings')
                ->middleware('can:cms.site_setting.view')
                ->name('settings');

            Route::view('/notices', 'tenant.cms.notices')
                ->middleware('can:cms.notice.view')
                ->name('notices');

            Route::view('/slider', 'tenant.cms.slider')
                ->middleware('can:cms.slider.view')
                ->name('slider');

            Route::view('/gallery', 'tenant.cms.gallery')
                ->middleware('can:cms.gallery.view')
                ->name('gallery');
        });

        Route::prefix('people')->name('people.')->group(function () {
            Route::view('/students', 'tenant.people.students')
                ->middleware('can:people.student.view')
                ->name('students');

            Route::view('/employees', 'tenant.people.employees')
                ->middleware('can:people.employee.view')
                ->name('employees');

            Route::view('/admissions', 'tenant.people.admissions')
                ->middleware('can:people.admission.view')
                ->name('admissions');

            Route::get('/admissions/{admission}/form.pdf', AdmissionFormPdfController::class)
                ->middleware('can:people.admission.print')
                ->name('admissions.form');
        });

        Route::prefix('finance')->name('finance.')->group(function () {
            Route::view('/fee-structures', 'tenant.finance.fee-structures')
                ->middleware('can:finance.fee_structure.view')
                ->name('fee-structures');

            Route::view('/invoices', 'tenant.finance.invoices')
                ->middleware('can:finance.invoice.view')
                ->name('invoices');

            Route::view('/collect', 'tenant.finance.collect')
                ->middleware('can:finance.payment.view')
                ->name('collect');

            Route::get('/payments/{payment}/receipt.pdf', ReceiptPdfController::class)
                ->middleware('can:finance.payment.receipt')
                ->name('receipt');
        });
    });
});

require __DIR__.'/tenant-public.php';
