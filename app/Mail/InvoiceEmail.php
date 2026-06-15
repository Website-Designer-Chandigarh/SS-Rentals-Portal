<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Services\InvoicePdfService;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class InvoiceEmail extends Mailable
{
    public function __construct(public Invoice $invoice, public string $customerEmail)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice ' . $this->invoice->id . ' - SS Rentals',
        );
    }

    public function content(): Content
    {
        $customerName = $this->invoice->customer->company ?? 'Valued Customer';
        
        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'customerName' => $customerName,
                'dueDate' => $this->invoice->due?->format('d M Y'),
                'total' => number_format($this->invoice->total ?? 0, 2),
            ],
        );
    }

    public function attachments(): array
    {
        try {
            $pdfService = new InvoicePdfService();
            $pdfContent = $pdfService->generatePdf($this->invoice);
            
            return [
                Attachment::fromData(
                    fn () => $pdfContent,
                    'Invoice-' . $this->invoice->id . '.html',
                    [
                        'mime' => 'text/html',
                    ]
                ),
            ];
        } catch (\Exception $e) {
            // If PDF generation fails, return no attachments
            return [];
        }
    }
}
