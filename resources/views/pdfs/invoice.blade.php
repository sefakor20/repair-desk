<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
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
            margin-bottom: 40px;
            border-bottom: 3px solid #7c3aed;
            padding-bottom: 20px;
        }

        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #7c3aed;
            margin-bottom: 5px;
        }

        .company-tagline {
            font-size: 12px;
            color: #666;
        }

        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            text-align: right;
            color: #333;
            margin-top: -40px;
        }

        .info-section {
            margin-bottom: 30px;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .info-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            color: #666;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 13px;
            margin-bottom: 15px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-paid {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-overdue {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-cancelled {
            background-color: #f3f4f6;
            color: #6b7280;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #7c3aed;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .items-table tr:last-child td {
            border-bottom: none;
        }

        .text-right {
            text-align: right;
        }

        .totals-section {
            float: right;
            width: 300px;
            margin-top: 20px;
        }

        .totals-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .totals-label {
            display: table-cell;
            text-align: right;
            padding-right: 20px;
            color: #666;
        }

        .totals-value {
            display: table-cell;
            text-align: right;
            font-weight: bold;
        }

        .totals-final {
            border-top: 2px solid #7c3aed;
            padding-top: 12px;
            margin-top: 12px;
            font-size: 16px;
        }

        .totals-final .totals-label {
            color: #333;
        }

        .payment-info {
            clear: both;
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .payment-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
        }

        .payment-table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .footer {
            margin-top: 60px;
            text-align: center;
            color: #666;
            font-size: 11px;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }

        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9fafb;
            border-left: 3px solid #7c3aed;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">RepairDesk</div>
            <div class="company-tagline">Professional Device Repair Services</div>
            <div class="invoice-title">INVOICE</div>
        </div>

        <!-- Invoice Info Grid -->
        <div class="info-grid">
            <div class="info-column">
                <div class="info-label">Bill To</div>
                <div class="info-value">
                    <strong>{{ $invoice->customer->name }}</strong><br>
                    @if ($invoice->customer->email)
                        {{ $invoice->customer->email }}<br>
                    @endif
                    @if ($invoice->customer->phone)
                        {{ $invoice->customer->phone }}<br>
                    @endif
                    @if ($invoice->customer->address)
                        {{ $invoice->customer->address }}
                    @endif
                </div>

                @if ($invoice->ticket)
                    <div class="info-label">Device Information</div>
                    <div class="info-value">
                        <strong>{{ $invoice->ticket->device->brand }} {{ $invoice->ticket->device->model }}</strong><br>
                        @if ($invoice->ticket->device->serial_number)
                            S/N: {{ $invoice->ticket->device->serial_number }}<br>
                        @endif
                        @if ($invoice->ticket->device->imei)
                            IMEI: {{ $invoice->ticket->device->imei }}
                        @endif
                    </div>
                @endif
            </div>

            <div class="info-column">
                <div class="info-label">Invoice Number</div>
                <div class="info-value">{{ $invoice->invoice_number }}</div>

                <div class="info-label">Invoice Date</div>
                <div class="info-value">{{ $invoice->created_at->format('F d, Y') }}</div>

                @if ($invoice->ticket)
                    <div class="info-label">Ticket Number</div>
                    <div class="info-value">{{ $invoice->ticket->ticket_number }}</div>
                @endif

                <div class="info-label">Status</div>
                <div class="info-value">
                    <span class="status-badge status-{{ $invoice->status->value }}">
                        {{ str($invoice->status->value)->title() }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right" style="width: 20%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @if ($invoice->ticket)
                    <!-- Labor Cost -->
                    @if ($invoice->ticket->labor_cost > 0)
                        <tr>
                            <td>
                                <strong>Labor</strong><br>
                                <span style="font-size: 11px; color: #666;">{{ $invoice->ticket->description }}</span>
                            </td>
                            <td class="text-right">GH₵ {{ number_format($invoice->ticket->labor_cost, 2) }}</td>
                        </tr>
                    @endif

                    <!-- Parts -->
                    @foreach ($invoice->ticket->parts as $part)
                        <tr>
                            <td>
                                <strong>{{ $part->name }}</strong><br>
                                <span style="font-size: 11px; color: #666;">
                                    Qty: {{ $part->pivot->quantity }} × GH₵
                                    {{ number_format($part->pivot->unit_price, 2) }}
                                </span>
                            </td>
                            <td class="text-right">GH₵
                                {{ number_format($part->pivot->quantity * $part->pivot->unit_price, 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>Service Charges</td>
                        <td class="text-right">GH₵ {{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-row">
                <div class="totals-label">Subtotal:</div>
                <div class="totals-value">GH₵ {{ number_format($invoice->subtotal, 2) }}</div>
            </div>

            @if ($invoice->discount > 0)
                <div class="totals-row">
                    <div class="totals-label">Discount:</div>
                    <div class="totals-value" style="color: #16a34a;">-GH₵ {{ number_format($invoice->discount, 2) }}
                    </div>
                </div>
            @endif

            @if ($invoice->tax_amount > 0)
                <div class="totals-row">
                    <div class="totals-label">Tax ({{ number_format($invoice->tax_rate, 1) }}%):</div>
                    <div class="totals-value">GH₵ {{ number_format($invoice->tax_amount, 2) }}</div>
                </div>
            @endif

            <div class="totals-row totals-final">
                <div class="totals-label">Total:</div>
                <div class="totals-value">GH₵ {{ number_format($invoice->total, 2) }}</div>
            </div>

            @if ($invoice->total_paid > 0)
                <div class="totals-row">
                    <div class="totals-label">Paid:</div>
                    <div class="totals-value" style="color: #16a34a;">GH₵ {{ number_format($invoice->total_paid, 2) }}
                    </div>
                </div>

                <div class="totals-row"
                    style="font-size: 14px; color: {{ $invoice->balance_due > 0 ? '#dc2626' : '#16a34a' }};">
                    <div class="totals-label">Balance Due:</div>
                    <div class="totals-value">GH₵ {{ number_format($invoice->balance_due, 2) }}</div>
                </div>
            @endif
        </div>

        <!-- Payment History -->
        @if ($invoice->payments->count() > 0)
            <div class="payment-info">
                <div class="payment-title">Payment History</div>
                <table class="payment-table">
                    <tbody>
                        @foreach ($invoice->payments as $payment)
                            <tr>
                                <td style="width: 30%;">{{ $payment->payment_date->format('M d, Y') }}</td>
                                <td style="width: 25%;">{{ $payment->payment_method->value }}</td>
                                <td style="width: 30%;">{{ $payment->transaction_reference }}</td>
                                <td class="text-right" style="width: 15%; font-weight: bold;">
                                    GH₵ {{ number_format($payment->amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Notes -->
        @if ($invoice->notes)
            <div class="notes">
                <div class="notes-title">Notes</div>
                <div>{{ $invoice->notes }}</div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for your business!</strong></p>
            <p>For questions about this invoice, please contact us at support@repairdesk.com</p>
            <p style="margin-top: 10px;">Powered by <strong>rCodez</strong>, www.rcodez.com</p>
        </div>
    </div>
</body>

</html>
