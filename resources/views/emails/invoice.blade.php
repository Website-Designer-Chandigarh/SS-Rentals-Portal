<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            background: #f5f5f5;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            border-bottom: 3px solid #1976D2;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #1976D2;
            font-size: 24px;
        }
        .invoice-details {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 30px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #666;
        }
        .detail-value {
            color: #333;
        }
        .summary-section {
            margin-bottom: 30px;
        }
        .summary-section h3 {
            color: #1976D2;
            font-size: 16px;
            margin-bottom: 15px;
            border-bottom: 2px solid #E3F2FD;
            padding-bottom: 10px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .summary-item:last-child {
            border-bottom: none;
        }
        .summary-item-label {
            color: #666;
        }
        .summary-item-amount {
            font-weight: 600;
            color: #333;
        }
        .total-section {
            background: linear-gradient(135deg, #E3F2FD 0%, #F5F5F5 100%);
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 30px;
            border-left: 4px solid #1976D2;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 18px;
            font-weight: 700;
            color: #1976D2;
        }
        .message-section {
            background: #FFF3E0;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #FF9800;
            margin-bottom: 30px;
            font-size: 14px;
            color: #E65100;
        }
        .footer {
            border-top: 1px solid #eee;
            padding-top: 20px;
            color: #999;
            font-size: 12px;
            text-align: center;
        }
        .footer-brand {
            font-weight: 600;
            color: #1976D2;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <h1>Invoice {{ $invoice->id }}</h1>
        </div>

        <p style="margin: 0 0 20px 0">Dear {{ $customerName }},</p>
        <p style="margin: 0 0 30px 0; color: #666; line-height: 1.6">Thank you for your business. Please find your invoice details below. An invoice PDF is attached for your records.</p>

        <div class="invoice-details">
            <div class="detail-row">
                <span class="detail-label">Invoice Number:</span>
                <span class="detail-value">{{ $invoice->id }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Invoice Date:</span>
                <span class="detail-value">{{ $invoice->date?->format('d M Y') ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Due Date:</span>
                <span class="detail-value">{{ $dueDate ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="summary-section">
            <h3>Invoice Summary</h3>
            @if($invoice->truck_hire > 0)
            <div class="summary-item">
                <span class="summary-item-label">Truck Hire</span>
                <span class="summary-item-amount">${{ number_format($invoice->truck_hire, 2) }}</span>
            </div>
            @endif
            @if($invoice->trailer_hire > 0)
            <div class="summary-item">
                <span class="summary-item-label">Trailer Hire</span>
                <span class="summary-item-amount">${{ number_format($invoice->trailer_hire, 2) }}</span>
            </div>
            @endif
            @if($invoice->mileage > 0)
            <div class="summary-item">
                <span class="summary-item-label">Mileage</span>
                <span class="summary-item-amount">${{ number_format($invoice->mileage, 2) }}</span>
            </div>
            @endif
            @if($invoice->ruc > 0)
            <div class="summary-item">
                <span class="summary-item-label">RUC</span>
                <span class="summary-item-amount">${{ number_format($invoice->ruc, 2) }}</span>
            </div>
            @endif
            @if($invoice->damage > 0)
            <div class="summary-item">
                <span class="summary-item-label">Damage/Extras</span>
                <span class="summary-item-amount">${{ number_format($invoice->damage, 2) }}</span>
            </div>
            @endif
        </div>

        <div class="total-section">
            <div class="total-row">
                <span>TOTAL DUE:</span>
                <span>${{ $total }}</span>
            </div>
        </div>

        <div class="message-section">
            Please arrange payment by the due date. If you have any questions or need clarification on any charges, please don't hesitate to contact us.
        </div>

        <p style="margin: 0; color: #666; font-size: 14px; line-height: 1.6">Thank you for choosing SS Rentals. We appreciate your business.</p>

        <div class="footer">
            <div class="footer-brand">SS Rentals Portal</div>
            <div style="margin-top: 10px">This is an automated email. Please do not reply directly to this message.</div>
        </div>
    </div>
</body>
</html>
