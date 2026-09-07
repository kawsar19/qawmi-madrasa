<?php

declare(strict_types=1);

namespace App\Services\Pdf;

use Mpdf\Config\ConfigVariables;
use Mpdf\Mpdf;
use Mpdf\MpdfException;

/**
 * বাংলা-সক্ষম mPDF ইনস্ট্যান্স তৈরি করে।
 *
 * Bengali PDF output is the single biggest source of embarrassing bugs in
 * this domain (plan risk #4):
 *
 *  - dompdf breaks conjuncts (ক্ষ, ঞ্চ, ষ্ট্র) outright and must never be used.
 *  - mPDF needs `useOTL => 0xFF` on the font so OpenType shaping runs; without
 *    it conjuncts render as separate glyphs with visible hasants.
 *  - Mixed Bengali+Arabic lines ("হেদায়া (الهداية)") need an Arabic font
 *    registered too, otherwise the Arabic half renders as empty boxes.
 *  - Numbers are converted in PHP via Bn::num(), never left to font features.
 */
class PdfFactory
{
    /**
     * @param  array<string, mixed>  $config
     *
     * @throws MpdfException
     */
    public function make(array $config = []): Mpdf
    {
        $defaults = (new ConfigVariables)->getDefaults();
        $fontDirs = $defaults['fontDir'];

        return new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'hindsiliguri',
            'default_font_size' => 11,
            'margin_top' => 12,
            'margin_bottom' => 12,
            'margin_left' => 12,
            'margin_right' => 12,

            'fontDir' => [...$fontDirs, storage_path('fonts')],

            // Only our own fonts are declared. Merging mPDF's default
            // `fontdata` map breaks Bengali BOLD text — mPDF then resolves
            // bold runs through its built-in substitution tables to DejaVu,
            // which has no Bengali glyphs, and headings print as tofu boxes.
            // Verified with `php artisan pdf:spike`.
            'fontdata' => [
                // Hind Siliguri (SIL OFL). Noto Sans Bengali was tried first
                // and rejected: mPDF's TTF parser cannot read its tables
                // ("GPOS Lookup Type 5, Format 3 not supported"). Verify any
                // replacement font with `php artisan pdf:spike` before
                // switching.
                'hindsiliguri' => [
                    'R' => 'HindSiliguri-Regular.ttf',
                    'B' => 'HindSiliguri-Bold.ttf',
                    // 0xFF enables all OpenType layout features — required for
                    // Bengali conjunct formation (ক্ষ, ঞ্চ, ষ্ট্র).
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ],
                // XB Riyaz ships with mPDF and is used by its own Arabic
                // config, so it is known to parse. Amiri was tried and
                // rejected for the same GPOS reason as Noto.
                'xbriyaz' => [
                    'R' => 'XB Riyaz.ttf',
                    'B' => 'XB RiyazBd.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ],
            ],

            // Let mPDF pick the Arabic font for Arabic runs inside a
            // Bengali paragraph.
            'autoScriptToLang' => true,
            'autoLangToFont' => true,

            'tempDir' => storage_path('app/mpdf'),

            ...$config,
        ]);
    }

    /**
     * একটি Blade ভিউ রেন্ডার করে PDF বাইট ফেরত দেয়।
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $config
     *
     * @throws MpdfException
     */
    public function renderView(string $view, array $data = [], array $config = []): string
    {
        $pdf = $this->make($config);
        $pdf->WriteHTML(view($view, $data)->render());

        return $pdf->Output('', 'S');
    }
}
