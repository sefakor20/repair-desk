<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ReceiptPdfController extends Controller
{
    public function __invoke(
        Customer $customer,
        string $token,
        Payment $payment,
    ): Response {
        // Validate the customer's portal access token
        if (! $customer->portal_access_token || $customer->portal_access_token !== $token) {
            abort(403, 'Invalid or expired access link.');
        }

        // Validate that the payment belongs to this customer (through invoice)
        $payment->load('invoice');
        if ($payment->invoice->customer_id !== $customer->id) {
            abort(403, 'Unauthorized access to payment receipt');
        }

        // Load necessary relationships for the receipt
        $payment->load([
            'invoice.customer',
            'invoice.ticket.device',
        ]);

        // Generate the receipt PDF
        $pdf = Pdf::loadView('pdfs.receipt', ['payment' => $payment]);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');

        // Return the PDF as a download
        return $pdf->download("receipt-{$payment->transaction_reference}.pdf");
    }
}
