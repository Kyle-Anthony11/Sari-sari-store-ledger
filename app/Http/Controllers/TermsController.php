<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;

class TermsController extends Controller
{
    public function download()
    {
        $pdf = Pdf::loadView('pdf.terms-and-conditions')
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);

        // Stream (show in browser) — change to ->download() to force download
        return $pdf->stream('terms-and-conditions.pdf');
    }
}
