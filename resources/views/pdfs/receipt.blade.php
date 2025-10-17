<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }

        .container {
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #16a34a;
            padding-bottom: 20px;
        }

        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #7c3aed;
            margin-bottom: 5px;
        }

        .receipt-title {
            font-size: 32px;
            font-weight: bold;
            color: #16a34a;
            margin-top: 10px;
        }

        .receipt-subtitle {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .success-badge {
            display: inline-block;
            background-color: #dcfce7;
            color: #166534;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            margin: 20px 0;
        }

        .info-section {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .info-grid {
            display: table;
            width: 100%;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            padding: 8px 20px 8px 0;
            color: #666;
            font-weight: 600;
            width: 40%;
        }

        .info-value {
            display: table-cell;
            padding: 8px 0;
            font-weight: bold;
        }

        .payment-amount {
            text-align: center;
            margin: 40px 0;
            padding: 30px;
            background-color: #dcfce7;
            border-radius: 8px;
        }

        .payment-label {
            font-size: 14px;
            color: #166534;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .payment-value {
            font-size: 48px;
            font-weight: bold;
            color: #16a34a;
        }

        .invoice-details {
            margin: 30px 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 8px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .details-table tr:last-child td {
            border-bottom: none;
        }

        .text-right {
            text-align: right;
        }

        .text-bold {
            font-weight: bold;
        }

        .footer {
            margin-top: 60px;
            text-align: center;
            color: #666;
            font-size: 11px;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }

        .thank-you {
            font-size: 18px;
            font-weight: bold;
            color: #16a34a;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">RepairDesk</div>
            <div class="receipt-title">PAYMENT RECEIPT</div>
            <div class="receipt-subtitle">Official Payment Confirmation</div>
            <div class="success-badge">✓ PAYMENT SUCCESSFUL</div>
        </div>

        <!-- Payment Information -->
        <div class="info-section">
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Receipt Date:</div>
                    <div class="info-value">{{ $payment->payment_date->format('F d, Y h:i A') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Transaction Reference:</div>
                    <div class="info-value">{{ $payment->transaction_reference }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Payment Method:</div>
                    <div class="info-value">{{ str($payment->payment_method->value)->title() }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Customer Name:</div>
                    <div class="info-value">{{ $payment->invoice->customer->name }}</div>
                </div>
                @if ($payment->invoice->customer->email)
                    <div class="info-row">
                        <div class="info-label">Customer Email:</div>
                        <div class="info-value">{{ $payment->invoice->customer->email }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Payment Amount -->
        <div class="payment-amount">
            <div class="payment-label">Amount Paid</div>
            <div class="payment-value">GH₵ {{ number_format($payment->amount, 2) }}</div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="section-title">Invoice Details</div>
            <table class="details-table">
                <tr>
                    <td style="width: 40%;">Invoice Number:</td>
                    <td class="text-bold">{{ $payment->invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td>Invoice Date:</td>
                    <td class="text-bold">{{ $payment->invoice->created_at->format('F d, Y') }}</td>
                </tr>
                @if ($payment->invoice->ticket)
                    <tr>
                        <td>Ticket Number:</td>
                        <td class="text-bold">{{ $payment->invoice->ticket->ticket_number }}</td>
                    </tr>
                    <tr>
                        <td>Device:</td>
                        <td class="text-bold">
                            {{ $payment->invoice->ticket->device->brand }}
                            {{ $payment->invoice->ticket->device->model }}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td>Invoice Total:</td>
                    <td class="text-bold">GH₵ {{ number_format($payment->invoice->total, 2) }}</td>
                </tr>
                <tr>
                    <td>Total Paid:</td>
                    <td class="text-bold" style="color: #16a34a;">
                        GH₵ {{ number_format($payment->invoice->total_paid, 2) }}
                    </td>
                </tr>
                <tr>
                    <td>Balance Due:</td>
                    <td class="text-bold"
                        style="color: {{ $payment->invoice->balance_due > 0 ? '#dc2626' : '#16a34a' }};">
                        GH₵ {{ number_format($payment->invoice->balance_due, 2) }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Payment Notes -->
        @if ($payment->notes)
            <div class="info-section" style="margin-top: 30px;">
                <div style="font-weight: bold; margin-bottom: 5px;">Payment Notes:</div>
                <div>{{ $payment->notes }}</div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">Thank You for Your Payment!</div>
            <p>This receipt was generated automatically and serves as proof of payment.</p>
            <p style="margin-top: 10px;">
                For questions about this payment, please contact us at support@repairdesk.com<br>
                or reference your transaction number: {{ $payment->transaction_reference }}
            </p>
            <p style="margin-top: 15px; font-size: 10px;">
                <strong>RepairDesk</strong> - Professional Device Repair Services<br>
                Powered by <strong>rCodez</strong>, www.rcodez.com
            </p>
        </div>
    </div>
</body>

</html>
