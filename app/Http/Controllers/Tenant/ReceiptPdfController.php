<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Models\Finance\Payment;
use App\Services\Finance\ReceiptPdf;
use Illuminate\Http\Response;

/**
 * আদায়ের রসিদ ডাউনলোড।
 *
 * Route-model binding resolves through TenantScope, so another madrasa's
 * receipt id simply 404s — no extra check needed.
 */
class ReceiptPdfController
{
    public function __invoke(Payment $payment, ReceiptPdf $pdf): Response
    {
        return response($pdf->render($payment), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$pdf->filename($payment).'"',
        ]);
    }
}
