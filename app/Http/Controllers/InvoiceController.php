<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceEmail;
use App\Models\Invoice;
use App\Services\InvoicePdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    public function downloadPdf(Invoice $invoice): Response
    {
        $pdfService = new InvoicePdfService();
        $pdfContent = $pdfService->generatePdf($invoice);

        // For now, return as HTML that can be printed
        // Once dompdf is installed, this will return a proper PDF
        return response($pdfContent, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => 'inline; filename="'.$invoice->id.'.html"',
        ]);
    }

    public function viewPdf(Invoice $invoice): Response
    {
        $pdfService = new InvoicePdfService();
        $pdfContent = $pdfService->generatePdf($invoice);

        return response($pdfContent, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
        ]);
    }

    public function sendEmail(Invoice $invoice): RedirectResponse
    {
        if (!$invoice->customer || !$invoice->customer->email) {
            return back()->with('error', 'Customer email address not found for invoice ' . $invoice->id);
        }

        try {
            Mail::to($invoice->customer->email)
                ->send(new InvoiceEmail($invoice, $invoice->customer->email));

            // Update invoice status if it's still draft
            if ($invoice->status === 'draft') {
                $invoice->update(['status' => 'sent']);
            }

            return back()->with('success', 'Invoice ' . $invoice->id . ' sent successfully to ' . $invoice->customer->email);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return back()->with('error', 'Failed to send invoice: ' . $e->getMessage());
        }
    }
}
