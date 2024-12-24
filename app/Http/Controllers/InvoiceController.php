<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function generateInvoice($invoiceId)
    {
        // Load the invoice data from the database
        $invoice = Invoice::findOrFail($invoiceId);

        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice]);

        // Stream the PDF to the browser
        return $pdf->stream('invoice_' . $invoice->id . '.pdf');
    }
}
