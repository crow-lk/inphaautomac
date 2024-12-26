<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;

class InvoiceController extends Controller
{
    public function generateInvoice($invoiceId)
    {
        $invoice = Invoice::with('invoiceItems')->findOrFail($invoiceId);
        $items = $invoice->invoiceItems;

        // Split items into chunks of 5
        $chunks = $items->chunk(5);

        // Create a new FPDI instance
        $pdf = new Fpdi();

        foreach ($chunks as $index => $chunk) {
            // Generate PDF for the current chunk
            $pdfChunk = PDF::loadView('pdf.invoice', [
                'invoice' => $invoice,
                'invoiceItems' => $chunk,
            ]);

            // Save the PDF to a temporary file
            $filePath = public_path("invoice/invoice_{$invoice->id}_part_{$index}.pdf");
            $pdfChunk->save($filePath);

            // Import the saved PDF into the FPDI instance
            $pageCount = $pdf->setSourceFile($filePath);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $pdf->addPage();
                $pdf->useTemplate($templateId);
            }
        }

        // Output the combined PDF
        $outputPath = public_path("invoice/invoice_{$invoice->id}.pdf");
        $pdf->Output($outputPath, 'F');  // Save the combined PDF to a file

        // Return the combined PDF as a download
        return response()->download($outputPath)->deleteFileAfterSend(true);
    }
}
