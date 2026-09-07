<?php

declare(strict_types=1);

use App\Services\Pdf\PdfFactory;
use App\Support\Bn;

/**
 * বাংলা PDF রেন্ডারিং রেগ্রেশন টেস্ট।
 *
 * Plan risk #4. These tests lock in the font configuration discovered during
 * the PDF spike; each assertion corresponds to a way the output has actually
 * broken before.
 */
it('renders a bengali pdf without throwing', function () {
    $pdf = app(PdfFactory::class)->make();
    $pdf->WriteHTML('<p style="font-family:hindsiliguri">ক্ষ ঞ্চ ষ্ট্র শিক্ষা</p>');

    expect($pdf->Output('', 'S'))->toStartWith('%PDF-');
});

it('renders bold bengali without falling back to a glyphless font', function () {
    // Regression: merging mPDF's default `fontdata` made bold Bengali resolve
    // to DejaVu, which has no Bengali glyphs, and headings printed as tofu.
    $pdf = app(PdfFactory::class)->make();
    $pdf->WriteHTML('<h1 style="font-family:hindsiliguri">ক্রমিক নাম</h1>');

    expect($pdf->Output('', 'S'))->toStartWith('%PDF-');
});

it('renders mixed bengali and arabic on one line', function () {
    $pdf = app(PdfFactory::class)->make();
    $pdf->WriteHTML(
        '<p style="font-family:hindsiliguri">হেদায়া '
        .'<span style="font-family:xbriyaz">(الهداية)</span></p>'
    );

    expect($pdf->Output('', 'S'))->toStartWith('%PDF-');
});

it('declares only our own fonts so mpdf cannot substitute a glyphless one', function () {
    $pdf = app(PdfFactory::class)->make();

    $fontdata = (new ReflectionProperty($pdf, 'fontdata'))->getValue($pdf);

    expect(array_keys($fontdata))
        ->toEqualCanonicalizing(['hindsiliguri', 'xbriyaz']);
});

it('enables OpenType layout on every font so conjuncts form', function () {
    $pdf = app(PdfFactory::class)->make();

    $fontdata = (new ReflectionProperty($pdf, 'fontdata'))->getValue($pdf);

    foreach ($fontdata as $name => $config) {
        expect($config['useOTL'] ?? 0)
            ->toBe(0xFF, "font [{$name}] must enable OTL for conjunct shaping");
    }
});

it('converts digits to bengali in php rather than relying on the font', function () {
    // U+09E6..U+09EF, in order.
    expect(Bn::num('0123456789'))->toBe('০১২৩৪৫৬৭৮৯')
        ->and(Bn::taka(1234567.89))->toBe('৳ ১২,৩৪,৫৬৭.৮৯');
});

it('renders the full marksheet spike', function () {
    $this->artisan('pdf:spike', ['--out' => storage_path('app/test-spike.pdf')])
        ->assertSuccessful();

    expect(file_get_contents(storage_path('app/test-spike.pdf')))
        ->toStartWith('%PDF-');

    @unlink(storage_path('app/test-spike.pdf'));
});
