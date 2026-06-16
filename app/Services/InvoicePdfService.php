<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Hire;
use App\Models\Invoice;
use App\Models\Vehicle;

class InvoicePdfService
{
    public function generatePdf(Invoice $invoice): string
    {
        $customer = Customer::find($invoice->customer_id);
        $hire = Hire::find($invoice->hire_id);
        $truck = $hire ? Vehicle::find($hire->truck_id) : null;
        $trailer = $hire ? Vehicle::find($hire->trailer_id) : null;

        $html = $this->generateHtmlContent($invoice, $customer, $hire, $truck, $trailer);

        // Use a simple HTML to PDF conversion using inline styles
        // For production, you should install dompdf: composer require dompdf/dompdf
        return $this->htmlToPdf($html, $invoice->id);
    }

    private function generateHtmlContent(Invoice $invoice, ?Customer $customer, ?Hire $hire, ?Vehicle $truck, ?Vehicle $trailer): string
    {
        $companyName = config('app.name', 'SS Rentals Ltd');
        $companyGst = env('COMPANY_GST', '123-456-789');
        $companyAddress = env('COMPANY_ADDRESS', 'Auckland, New Zealand');
        $companyPhone = env('COMPANY_PHONE', '(09) 555 0123');
        $companyEmail = env('COMPANY_EMAIL', 'info@ssrentals.co.nz');

        $formattedDate = $invoice->date ? $invoice->date->format('d M Y') : date('d M Y');
        $formattedDue = $invoice->due ? $invoice->due->format('d M Y') : date('d M Y');

        $subtotal = $invoice->truck_hire + $invoice->trailer_hire + $invoice->mileage + $invoice->ruc + $invoice->damage + $invoice->extras;
        $gstRate = (float) env('GST_RATE', 15);
        $gst = round($subtotal * ($gstRate / 100), 2);
        $total = $subtotal + $gst;

        $html = <<<'HTML'
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: 'Arial', sans-serif; color: #333; background: #fff; line-height: 1.4; }
                .page { width: 210mm; height: 297mm; margin: 0 auto; padding: 20mm; background: white; }
                
                .header { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
                .company-logo { font-size: 24px; font-weight: bold; color: #003366; }
                .company-details { font-size: 11px; color: #666; line-height: 1.6; }
                
                .invoice-title { text-align: right; font-size: 20px; font-weight: bold; color: #003366; margin-bottom: 10px; }
                .invoice-meta { text-align: right; font-size: 11px; }
                .meta-row { display: grid; grid-template-columns: 100px 1fr; gap: 10px; margin-bottom: 3px; }
                .meta-label { font-weight: bold; color: #333; }
                .meta-value { color: #666; }
                
                .parties { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin: 20px 0; font-size: 11px; }
                .party { }
                .party-label { font-weight: bold; font-size: 10px; color: #666; margin-bottom: 5px; text-transform: uppercase; }
                .party-name { font-weight: bold; margin-bottom: 3px; }
                .party-details { color: #666; line-height: 1.5; }
                
                .items-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 11px; }
                .items-table th { background: #f0f0f0; padding: 8px; text-align: left; font-weight: bold; color: #333; border-bottom: 2px solid #333; }
                .items-table td { padding: 8px; border-bottom: 1px solid #ddd; }
                .items-table tr:last-child td { border-bottom: 2px solid #333; }
                .amount { text-align: right; }
                .qty { text-align: center; }
                
                .totals { margin: 20px 0; }
                .total-row { display: grid; grid-template-columns: 1fr 120px; gap: 20px; padding: 6px 0; font-size: 11px; }
                .total-row.subtotal { color: #666; }
                .total-row.tax { color: #666; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
                .total-row.final { font-weight: bold; font-size: 13px; color: #003366; padding-top: 10px; margin-top: 10px; border-top: 2px solid #333; }
                
                .notes { background: #f9f9f9; padding: 10px; border-left: 3px solid #003366; margin: 20px 0; font-size: 10px; color: #666; }
                .notes-title { font-weight: bold; color: #333; margin-bottom: 5px; }
                
                .payment-section { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin: 20px 0; font-size: 10px; }
                .payment-method { }
                .payment-title { font-weight: bold; color: #333; margin-bottom: 8px; }
                .payment-details { color: #666; line-height: 1.8; }
                
                .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; text-align: center; font-size: 9px; color: #999; }
            </style>
        </head>
        <body>
            <div class="page">
                <div class="header">
                    <div>
                        <div class="company-logo">COMPANY_NAME
                            <img src="COMPANY_LOGO_URL" alt="Company Logo" style="max-height: 50px; display: block; margin-top: 5px;">
                        </div>
                        <div class="company-details">
                            <div>COMPANY_ADDRESS</div>
                            <div>Phone: COMPANY_PHONE</div>
                            <div>Email: COMPANY_EMAIL</div>
                            <div style="margin-top: 8px;">GST Registration: COMPANY_GST</div>
                        </div>
                    </div>
                    <div>
                        <div class="invoice-title">INVOICE</div>
                        <div class="invoice-meta">
                            <div class="meta-row">
                                <span class="meta-label">Invoice #:</span>
                                <span class="meta-value">INVOICE_ID</span>
                            </div>
                            <div class="meta-row">
                                <span class="meta-label">Date:</span>
                                <span class="meta-value">FORMATTED_DATE</span>
                            </div>
                            <div class="meta-row">
                                <span class="meta-label">Due Date:</span>
                                <span class="meta-value">FORMATTED_DUE</span>
                            </div>
                            <div class="meta-row">
                                <span class="meta-label">Status:</span>
                                <span class="meta-value" style="font-weight: bold;">INVOICE_STATUS</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="parties">
                    <div class="party">
                        <div class="party-label">Billed To</div>
                        <div class="party-name">CUSTOMER_COMPANY</div>
                        <div class="party-details">
                            <div>Contact: CUSTOMER_CONTACT</div>
                            <div>Phone: CUSTOMER_PHONE</div>
                            <div>Email: CUSTOMER_EMAIL</div>
                        </div>
                    </div>
                    <div class="party">
                        <div class="party-label">Hire Reference</div>
                        <div class="party-name">HIRE_ID</div>
                        <div class="party-details">
                            <div>Vehicle: TRUCK_REGO TRAILER_INFO</div>
                            <div style="margin-top: 8px;">Period: INVOICE_PERIOD</div>
                        </div>
                    </div>
                </div>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Description</th>
                            <th class="qty" style="width: 10%;">Qty</th>
                            <th class="amount" style="width: 20%;">Unit Price</th>
                            <th class="amount" style="width: 20%;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Truck Hire (Weekly Rate)</td>
                            <td class="qty">1</td>
                            <td class="amount">$TRUCK_HIRE</td>
                            <td class="amount"><strong>$TRUCK_HIRE</strong></td>
                        </tr>
                        TRAILER_ROW
                        <tr>
                            <td>Mileage Charges</td>
                            <td class="qty">1</td>
                            <td class="amount">$MILEAGE</td>
                            <td class="amount"><strong>$MILEAGE</strong></td>
                        </tr>
                        <tr>
                            <td>RUC Charges (Road User Charges)</td>
                            <td class="qty">1</td>
                            <td class="amount">$RUC</td>
                            <td class="amount"><strong>$RUC</strong></td>
                        </tr>
                        DAMAGE_ROW
                        EXTRAS_ROW
                    </tbody>
                </table>

                <div class="totals">
                    <div class="total-row subtotal">
                        <span>Subtotal (excluding GST)</span>
                        <span class="amount">$SUBTOTAL</span>
                    </div>
                    <div class="total-row tax">
                        <span>GST (Goods & Services Tax @ GST_RATE%)</span>
                        <span class="amount">$GST</span>
                    </div>
                    <div class="total-row final">
                        <span>TOTAL DUE</span>
                        <span class="amount">$TOTAL</span>
                    </div>
                </div>

                <div class="notes">
                    <div class="notes-title">Payment Terms & Conditions</div>
                    <div>• Payment is due by FORMATTED_DUE</div>
                    <div>• Please remit payment to the account details provided in your email</div>
                    <div>• Late payment may result in additional charges and suspension of services</div>
                    <div>• All amounts shown are in NZD (New Zealand Dollars)</div>
                </div>

                <div class="payment-section">
                    <div class="payment-method">
                        <div class="payment-title">Payment Methods Accepted</div>
                        <div class="payment-details">
                            Bank Transfer (Preferred)<br>
                            Credit Card<br>
                            Direct Debit<br>
                            Cheque
                        </div>
                    </div>
                    <div class="payment-method">
                        <div class="payment-title">Contact for Payment Queries</div>
                        <div class="payment-details">
                            Phone: COMPANY_PHONE<br>
                            Email: COMPANY_EMAIL<br>
                            Hours: Mon-Fri 8am-5pm NZST
                        </div>
                    </div>
                </div>

                <div class="footer">
                    <p>This is an automatically generated invoice from SS Rentals Portal. Thank you for your business!</p>
                    <p style="margin-top: 8px;">© YEAR COMPANY_NAME. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;

        // Build trailer row if needed
        $trailerRow = $invoice->trailer_hire > 0 ? <<<'TRAIL'
                        <tr>
                            <td>Trailer Hire</td>
                            <td class="qty">1</td>
                            <td class="amount">$TRAILER_HIRE</td>
                            <td class="amount"><strong>$TRAILER_HIRE</strong></td>
                        </tr>
        TRAIL : '';

        $trailerInfo = $invoice->trailer_hire > 0 ? '+ TRAILER_REGO' : '';

        $damageRow = $invoice->damage > 0 ? <<<'DMG'
                        <tr>
                            <td>Damage Charges</td>
                            <td class="qty">1</td>
                            <td class="amount">$DAMAGE</td>
                            <td class="amount"><strong>$DAMAGE</strong></td>
                        </tr>
        DMG : '';

        $extrasRow = $invoice->extras > 0 ? <<<'EXT'
                        <tr>
                            <td>Additional Charges</td>
                            <td class="qty">1</td>
                            <td class="amount">$EXTRAS</td>
                            <td class="amount"><strong>$EXTRAS</strong></td>
                        </tr>
        EXT : '';

        // Status label
        $statusLabel = match($invoice->status) {
            'paid' => 'PAID',
            'sent' => 'SENT',
            'draft' => 'DRAFT',
            'overdue' => 'OVERDUE',
            default => strtoupper($invoice->status)
        };

        $html = strtr($html, [
            'COMPANY_LOGO_URL' => asset('images/logo.png'),
            'COMPANY_NAME' => $companyName,
            'COMPANY_ADDRESS' => $companyAddress,
            'COMPANY_PHONE' => $companyPhone,
            'COMPANY_EMAIL' => $companyEmail,
            'COMPANY_GST' => $companyGst,
            'INVOICE_ID' => $invoice->id,
            'FORMATTED_DATE' => $formattedDate,
            'FORMATTED_DUE' => $formattedDue,
            'INVOICE_STATUS' => $statusLabel,
            'CUSTOMER_COMPANY' => $customer?->company ?? 'Customer',
            'CUSTOMER_CONTACT' => $customer?->contact ?? '',
            'CUSTOMER_PHONE' => $customer?->phone ?? '',
            'CUSTOMER_EMAIL' => $customer?->email ?? '',
            'HIRE_ID' => $hire?->id ?? '',
            'TRUCK_REGO' => $truck?->rego ?? '',
            'TRAILER_INFO' => $trailerInfo,
            'TRUCK_HIRE' => number_format($invoice->truck_hire, 2),
            'TRAILER_ROW' => $trailerRow,
            'TRAILER_HIRE' => number_format($invoice->trailer_hire, 2),
            'TRAILER_REGO' => $trailer?->rego ?? '',
            'MILEAGE' => number_format($invoice->mileage, 2),
            'RUC' => number_format($invoice->ruc, 2),
            'DAMAGE_ROW' => $damageRow,
            'DAMAGE' => number_format($invoice->damage, 2),
            'EXTRAS_ROW' => $extrasRow,
            'EXTRAS' => number_format($invoice->extras, 2),
            'INVOICE_PERIOD' => $invoice->period ?? 'Not specified',
            'SUBTOTAL' => number_format($subtotal, 2),
            'GST_RATE' => $gstRate,
            'GST' => number_format($gst, 2),
            'TOTAL' => number_format($total, 2),
            'YEAR' => date('Y'),
        ]);

        return $html;
    }

    private function htmlToPdf(string $html, string $invoiceId): string
    {
        try {
            // Try to use dompdf if available
            if (class_exists('Dompdf\Dompdf')) {
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4');
                $dompdf->render();
                return $dompdf->output();
            }
        } catch (\Exception $e) {
            // Fall back to basic implementation
        }

        // For now, return HTML as-is for browser rendering
        // In production, install dompdf for proper PDF generation
        return $html;
    }
}
