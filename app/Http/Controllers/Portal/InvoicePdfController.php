<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\{Customer, Invoice};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoicePdfController extends Controller
{
    public function __invoke(Customer $customer, string $token, Invoice $invoice): Response
    {
        // Validate customer token
        if (! $customer->portal_access_token || $customer->portal_access_token !== $token) {
            abort(403, 'Invalid or expired access link.');
        }

        // Ensure invoice belongs to customer
        if ($invoice->customer_id !== $customer->id) {
            abort(403, 'Unauthorized access to invoice');
        }

        // Load relationships
        $invoice->load([
            'customer',
            'ticket.device',
            'ticket.parts',
            'payments',
        ]);

        // Generate PDF
        $pdf = Pdf::loadView('pdfs.invoice', [
            'invoice' => $invoice,
        ]);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');

        // Return PDF for download
        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}
