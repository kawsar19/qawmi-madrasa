<?php

declare(strict_types=1);

namespace App\Services\People;

use App\Models\Cms\SiteSetting;
use App\Models\People\Admission;
use App\Services\Pdf\PdfFactory;
use App\Support\Media;
use Illuminate\Support\Facades\Storage;
use Mpdf\MpdfException;

/**
 * ভর্তি ফর্ম PDF — আবেদনকারীর তথ্য বসানো, ছাপার উপযোগী।
 *
 * The madrasa hands this to the guardian and keeps a copy in the file, so it
 * has to survive a photocopier: solid rules, no light-grey text, and the
 * signature block never orphaned onto a second page.
 */
class AdmissionFormPdf
{
    public function __construct(private readonly PdfFactory $factory) {}

    /**
     * @throws MpdfException
     */
    public function render(Admission $admission): string
    {
        $admission->loadMissing(['jamaat', 'academicSession']);

        $settings = SiteSetting::current();

        return $this->factory->renderView('pdf.admission-form', [
            'admission' => $admission,
            'settings' => $settings,
            'madrasa' => $settings->displayTitle(),
            'logo' => $this->logoPath($settings->logo_path),
            'brand' => $this->hex($settings->brand_color, '#15803d'),
            'accent' => $this->hex($settings->accent_color, '#a16207'),
        ]);
    }

    /**
     * ডাউনলোডের ফাইলনাম — আবেদন নম্বর ধরে।
     */
    public function filename(Admission $admission): string
    {
        return 'admission-'.$admission->application_no.'.pdf';
    }

    /**
     * mPDF-এর জন্য লোগোর স্থানীয় ফাইলপাথ।
     *
     * mPDF fetches a remote <img src> over HTTP, which on a local dev domain
     * (*.app.localhost) resolves to nothing and silently drops the image. A
     * local disk therefore hands over the absolute path instead; a remote
     * disk has no path, so its URL is the only option.
     */
    private function logoPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (Media::disk() !== 'public') {
            return Media::url($path);
        }

        $absolute = Storage::disk('public')->path($path);

        return is_file($absolute) ? $absolute : null;
    }

    /**
     * বৈধ hex রঙ, নইলে ডিফল্ট — সেটিংসের রঙ সরাসরি CSS-এ বসে।
     */
    private function hex(?string $color, string $fallback): string
    {
        return is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1
            ? $color
            : $fallback;
    }
}
