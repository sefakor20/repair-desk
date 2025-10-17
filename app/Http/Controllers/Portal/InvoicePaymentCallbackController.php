<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Enums\{InvoiceStatus, PaymentMethod};
use App\Http\Controllers\Controller;
use App\Mail\PaymentReceiptMail;
use App\Models\{Customer, Invoice, Payment, User};
use App\Services\PaystackService;
use Exception;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Mail;

class InvoicePaymentCallbackController extends Controller
{
    public function __invoke(
        Request $request,
        Customer $customer,
        string $token,
        Invoice $invoice,
        PaystackService $paystackService,
    ): RedirectResponse {
        // Validate customer token
        if (! $customer->portal_access_token || $customer->portal_access_token !== $token) {
            return redirect()
                ->route('portal.login')
                ->with('error', 'Invalid or expired access link.');
        }

        // Ensure invoice belongs to customer
        if ($invoice->customer_id !== $customer->id) {
            abort(403, 'Unauthorized access to invoice');
        }

        $reference = $request->query('reference');

        if (! $reference) {
            return redirect()
                ->route('portal.invoices.index', [
                    'customer' => $customer->id,
                    'token' => $token,
                ])
                ->with('error', 'Payment reference is missing');
        }

        try {
            // Verify the transaction with Paystack
            $response = $paystackService->verifyTransaction($reference);

            if ($response['status'] && $response['data']['status'] === 'success') {
                $amountPaid = $response['data']['amount'] / 100; // Convert from kobo/pesewas

                // Create payment record
                // Use first user as system processor for automated payments
                $systemUser = User::first();

                $payment = Payment::create([
                    'invoice_id' => $invoice->id,
                    'ticket_id' => $invoice->ticket_id,
                    'amount' => $amountPaid,
                    'payment_method' => PaymentMethod::Card,
                    'payment_date' => now(),
                    'transaction_reference' => $reference,
                    'processed_by' => $systemUser?->id,
                    'notes' => 'Online payment via Paystack - Customer Portal',
                ]);

                // Update invoice status if fully paid
                if ($invoice->fresh()->balance_due <= 0) {
                    $invoice->update(['status' => InvoiceStatus::Paid]);
                }

                // Send receipt email if customer has email
                if ($customer->email) {
                    Mail::to($customer->email)->send(new PaymentReceiptMail($payment));
                }

                // Redirect to ticket show with success message and receipt link
                if ($invoice->ticket_id) {
                    return redirect()
                        ->route('portal.tickets.show', [
                            'customer' => $customer->id,
                            'token' => $token,
                            'ticket' => $invoice->ticket_id,
                        ])
                        ->with('success', 'Payment completed successfully! GH₵ ' . number_format($amountPaid, 2) . ' has been paid.')
                        ->with('payment_id', $payment->id)
                        ->with('show_receipt', true);
                }

                return redirect()
                    ->route('portal.invoices.index', [
                        'customer' => $customer->id,
                        'token' => $token,
                    ])
                    ->with('success', 'Payment completed successfully! GH₵ ' . number_format($amountPaid, 2) . ' has been paid.');
            } else {
                return redirect()
                    ->route('portal.invoices.index', [
                        'customer' => $customer->id,
                        'token' => $token,
                    ])
                    ->with('error', 'Payment verification failed: ' . ($response['message'] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            return redirect()
                ->route('portal.invoices.index', [
                    'customer' => $customer->id,
                    'token' => $token,
                ])
                ->with('error', 'Payment verification error: ' . $e->getMessage());
        }
    }
}
