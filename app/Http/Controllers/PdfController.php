<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
   public function export( $request, $payments = null)
    {
        $transaction = $payments ?? [];

        $pdf = Pdf::loadView('pdf.invoice', [
            'headerNote' => '',
            'transaction' => $transaction,
        ]);

        return $pdf->download('payments.pdf');
    }
    public function exportBookingsReport($bookings = null)
    {
        $transaction = $bookings ?? [];

        $pdf = Pdf::loadView('pdf.reports', [
            'headerNote' => '',
            'transaction' => $transaction,
        ]);

        return $pdf->download('payments.pdf');
    }
}
