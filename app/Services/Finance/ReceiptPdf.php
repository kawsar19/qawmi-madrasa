<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Models\Cms\SiteSetting;
use App\Models\Finance\Payment;
use App\Services\Pdf\PdfFactory;
use App\Support\Media;
use Illuminate\Support\Facades\Storage;

/**
 * আদায়ের রসিদ PDF।
 *
 * অভিভাবকের হাতে যাওয়া কাগজ — তাই বাতিল রসিদেও ছাপা হয়, জলছাপ সহ,
 * কারণ পুরনো কপি মিলিয়ে দেখতে হয়।
 */
class ReceiptPdf
{
    public function __construct(private readonly PdfFactory $factory) {}

    public function render(Payment $payment): string
    {
        $payment->loadMissing(['student', 'allocations.invoice', 'receivedBy']);

        $settings = SiteSetting::current();

        return $this->factory->renderView('pdf.receipt', [
            'payment' => $payment,
            'settings' => $settings,
            'madrasa' => $settings->displayTitle(),
            'logo' => $this->logoPath($settings->logo_path),
            'brand' => $this->hex($settings->brand_color, '#15803d'),
            'accent' => $this->hex($settings->accent_color, '#a16207'),
        ]);
    }

    public function filename(Payment $payment): string
    {
        return 'receipt-'.$payment->receipt_no.'.pdf';
    }

    /**
     * mPDF-এর জন্য লোগোর স্থানীয় ফাইলপাথ।
     *
     * mPDF fetches a remote <img src> over HTTP, which on a local dev domain
     * (*.app.localhost) resolves to nothing and silently drops the image.
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
