<?php

declare(strict_types=1);

use App\Models\Academic\AcademicSession;
use App\Models\Academic\Jamaat;
use App\Models\Academic\Marhala;
use App\Models\Central\Tenant;
use App\Models\Cms\SiteSetting;
use App\Models\People\Admission;
use App\Models\User;
use App\Services\People\AdmissionFormPdf;
use App\Services\People\AdmissionService;
use App\Services\Tenancy\TenantProvisioner;

afterEach(fn () => tenancy()->end());

// Pest helpers are file-scoped, so AdmissionTest's cannot be reused here.
function formPdfTenant(string $slug = 'darul-ulum'): Tenant
{
    $tenant = app(TenantProvisioner::class)->provision(
        ['name' => 'দারুল উলুম মাদরাসা', 'slug' => $slug, 'madrasa_type' => 'kitab'],
        "{$slug}.localhost",
        [
            'name' => 'মুহতামিম সাহেব',
            'email' => "admin@{$slug}.test",
            'password' => 'password',
            'mobile' => '01712345678',
        ],
    );

    $tenant->update(['trial_ends_at' => now()->addMonth()]);

    return $tenant;
}

function formPdfAdmin(Tenant $tenant): User
{
    tenancy()->initialize($tenant);
    $user = User::query()->where('tenant_id', $tenant->getKey())->firstOrFail();
    tenancy()->end();

    return $user;
}

/** টেন্যান্ট কনটেক্সট চালু রেখে একটি আবেদন তৈরি করে। */
function formPdfApplication(Tenant $tenant): Admission
{
    tenancy()->initialize($tenant);

    $session = AcademicSession::create(['name' => '১৪৪৬-১৪৪৭ হিজরি', 'is_current' => true]);
    $marhala = Marhala::where('code', 'hifz')->firstOrFail();
    $jamaat = Jamaat::create(['marhala_id' => $marhala->id, 'name' => '১০ পারা']);

    return app(AdmissionService::class)->apply([
        'academic_session_id' => $session->id,
        'jamaat_id' => $jamaat->id,
        'name' => 'আব্দুল্লাহ',
        'father_name' => 'আব্দুর রহমান',
        'district' => 'কুমিল্লা',
        'guardian_name' => 'আব্দুর রহমান',
        'guardian_mobile' => '01712345678',
    ]);
}

/**
 * ভর্তি ফর্ম PDF।
 *
 * The form is handed to the guardian and filed as the office copy, so the
 * failures that matter are: it does not render at all, it renders for the
 * wrong madrasa, or an unprivileged user can pull one.
 */
it('renders the admission form as a pdf', function () {
    $tenant = formPdfTenant();
    $admission = formPdfApplication($tenant);

    $bytes = app(AdmissionFormPdf::class)->render($admission);

    expect($bytes)->toStartWith('%PDF-')
        ->and(strlen($bytes))->toBeGreaterThan(1000);
});

it('renders inside tenant context, where storage_path is suffixed', function () {
    // Regression: fontDir used storage_path('fonts'), which tenancy rewrites
    // to storage/tenant{id}/fonts — a directory that does not exist. Every
    // in-panel download died with "Cannot find TTF TrueType font file", while
    // `pdf:spike` passed because it runs on the central domain.
    $tenant = formPdfTenant();
    $admission = formPdfApplication($tenant);

    expect(tenancy()->initialized)->toBeTrue()
        ->and(app(AdmissionFormPdf::class)->render($admission))->toStartWith('%PDF-');
});

it('names the download after the application number', function () {
    $tenant = formPdfTenant();
    $admission = formPdfApplication($tenant);

    expect(app(AdmissionFormPdf::class)->filename($admission))
        ->toBe('admission-'.$admission->application_no.'.pdf');
});

it('falls back to a safe colour when the brand colour is not a hex value', function () {
    $tenant = formPdfTenant();
    $admission = formPdfApplication($tenant);

    // brand_color lands in a CSS declaration verbatim; a junk value would
    // otherwise break the stylesheet for the whole document.
    SiteSetting::current()->update(['brand_color' => 'red; } body { display:none']);

    expect(app(AdmissionFormPdf::class)->render($admission))->toStartWith('%PDF-');
});

it('downloads the form from the panel', function () {
    $tenant = formPdfTenant();
    $admission = formPdfApplication($tenant);
    tenancy()->end();

    $response = $this->actingAs(formPdfAdmin($tenant))
        ->get("http://darul-ulum.localhost/panel/people/admissions/{$admission->id}/form.pdf");

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertDownload('admission-'.$admission->application_no.'.pdf');
});

it('does not serve an admission belonging to another madrasa', function () {
    $other = formPdfTenant('other-madrasa');
    $foreign = formPdfApplication($other);
    tenancy()->end();

    $tenant = formPdfTenant();
    tenancy()->end();

    // Route-model binding runs through the tenant scope, so the foreign id
    // must not resolve — otherwise one madrasa reads another's applicants.
    $this->actingAs(formPdfAdmin($tenant))
        ->get("http://darul-ulum.localhost/panel/people/admissions/{$foreign->id}/form.pdf")
        ->assertNotFound();
});

it('refuses a user without the print permission', function () {
    $tenant = formPdfTenant();
    $admission = formPdfApplication($tenant);

    $clerk = User::create([
        'tenant_id' => $tenant->getKey(),
        'name' => 'কেরানী',
        'email' => 'clerk@darul-ulum.test',
        'password' => 'password',
        'mobile' => '01812345678',
    ]);
    tenancy()->end();

    $this->actingAs($clerk)
        ->get("http://darul-ulum.localhost/panel/people/admissions/{$admission->id}/form.pdf")
        ->assertForbidden();
});
