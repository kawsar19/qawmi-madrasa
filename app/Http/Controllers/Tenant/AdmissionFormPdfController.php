<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Models\People\Admission;
use App\Services\People\AdmissionFormPdf;
use Illuminate\Http\Response;

/**
 * ভর্তি ফর্ম PDF ডাউনলোড।
 *
 * Route-model binding runs through the tenant global scope, so an admission
 * id from another madrasa 404s rather than leaking a form.
 */
class AdmissionFormPdfController
{
    public function __invoke(Admission $admission, AdmissionFormPdf $pdf): Response
    {
        return response($pdf->render($admission), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$pdf->filename($admission).'"',
        ]);
    }
}
