<?php

declare(strict_types=1);

namespace App\Livewire\Portal\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\{Customer, Invoice};
use App\Services\PaystackService;
use Exception;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal-fullpage')]
class PayInvoice extends Component
{
    public Customer $customer;

    public Invoice $invoice;

    public string $email = '';

    public bool $paymentInitialized = false;

    public ?string $paymentReference = null;

    public ?string $errorMessage = null;

    public function mount(Customer $customer, Invoice $invoice): void
    {
        // Ensure invoice belongs to customer
        if ($invoice->customer_id !== $customer->id) {
            abort(403, 'Unauthorized access to invoice');
        }

        // Check if invoice can be paid
        if ($invoice->status === InvoiceStatus::Paid || $invoice->balance_due <= 0) {
            session()->flash('error', 'This invoice has already been paid.');
            $this->redirect(route('portal.invoices.index', [
                'customer' => $customer->id,
                'token' => $customer->portal_access_token,
            ]), navigate: true);

            return;
        }

        $this->customer = $customer;
        $this->invoice = $invoice;
        $this->email = $customer->email ?? '';

        // Ensure customer has a portal access token
        if (! $customer->portal_access_token) {
            $customer->generatePortalAccessToken();
        }
    }

    public function initializePayment(PaystackService $paystackService): void
    {
        $this->validate([
            'email' => 'required|email',
        ]);

        try {
            $response = $paystackService->initializeTransaction([
                'amount' => $this->invoice->balance_due * 100, // Convert to kobo/pesewas
                'email' => $this->email,
                'reference' => $paystackService->generateReference(),
                'callback_url' => route('portal.invoices.payment.callback', [
                    'customer' => $this->customer->id,
                    'token' => $this->customer->portal_access_token,
                    'invoice' => $this->invoice->id,
                ]),
                'metadata' => [
                    'invoice_id' => $this->invoice->id,
                    'invoice_number' => $this->invoice->invoice_number,
                    'customer_id' => $this->customer->id,
                    'custom_fields' => [
                        [
                            'display_name' => 'Invoice Number',
                            'variable_name' => 'invoice_number',
                            'value' => $this->invoice->invoice_number,
                        ],
                        [
                            'display_name' => 'Customer Name',
                            'variable_name' => 'customer_name',
                            'value' => $this->customer->name,
                        ],
                    ],
                ],
            ]);

            if ($response['status']) {
                $this->paymentReference = $response['data']['reference'];
                $this->paymentInitialized = true;

                // Redirect to Paystack payment page
                $this->dispatch('redirect-to-paystack', url: $response['data']['authorization_url']);
            } else {
                $this->errorMessage = $response['message'] ?? 'Failed to initialize payment';
            }
        } catch (Exception $e) {
            $this->errorMessage = 'Payment initialization failed: ' . $e->getMessage();
        }
    }

    public function render(): View
    {
        $this->invoice->load(['ticket.device', 'payments']);

        return view('livewire.portal.invoices.pay-invoice');
    }
}
