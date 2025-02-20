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

        // Create a new FPDI instance
        $pdf = new Fpdi();
        $itemCount = $items->count();
        $itemsPerPage = 15; // Number of items per PDF
        $currentPage = 0;

        // Loop until all items are processed
        while ($currentPage * $itemsPerPage < $itemCount) {
            // Get the current chunk of items
            $chunk = $items->slice($currentPage * $itemsPerPage, $itemsPerPage);

            // Determine if this is the last chunk
            $isLastChunk = ($currentPage + 1) * $itemsPerPage >= $itemCount;

            // Generate PDF for the current chunk
            $pdfChunk = PDF::loadView('pdf.invoice', [
                'invoice' => $invoice,
                'invoiceItems' => $chunk, // Pass the current chunk
                'showGrandTotal' => $isLastChunk, // Set to true if this is the last chunk
            ]);

            // Save the PDF to a temporary file
            $filePath = public_path("invoice/invoice_{$invoice->id}_part_{$currentPage}.pdf");
            $pdfChunk->save($filePath);

            // Import the saved PDF into the FPDI instance
            $pageCount = $pdf->setSourceFile($filePath);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $pdf->addPage();
                $pdf->useTemplate($templateId);
            }

            $currentPage++; // Move to the next page
        }

        // Output the combined PDF
        $outputPath = public_path("invoice/invoice_{$invoice->id}.pdf");
        $pdf->Output($outputPath, 'F'); // Save the combined PDF to a file

        // Return the combined PDF as a download
        return response()->file($outputPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="invoice_' . $invoice->id . '.pdf"',
        ])->deleteFileAfterSend(true);
    }
}
