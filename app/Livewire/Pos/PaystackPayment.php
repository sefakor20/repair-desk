<?php

declare(strict_types=1);

namespace App\Livewire\Pos;

use App\Models\PosSale;
use App\Services\PaystackService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Exception;

class PaystackPayment extends Component
{
    public PosSale $sale;

    public string $email = '';

    public bool $paymentInitialized = false;

    public ?string $paymentReference = null;

    public ?string $errorMessage = null;

    public function mount(PosSale $sale): void
    {
        $this->sale = $sale;

        // Pre-fill email from customer or user
        if ($this->sale->customer?->email) {
            $this->email = $this->sale->customer->email;
        } else {
            $this->email = Auth::user()->email ?? '';
        }
    }

    public function initializePayment(PaystackService $paystackService): void
    {
        $this->validate([
            'email' => 'required|email',
        ]);

        try {
            $response = $paystackService->initializeTransaction([
                'amount' => $this->sale->total_amount * 100, // Convert to kobo/pesewas
                'email' => $this->email,
                'reference' => $paystackService->generateReference(),
                'callback_url' => route('pos.paystack.callback', ['sale' => $this->sale->id]),
                'metadata' => [
                    'sale_id' => $this->sale->id,
                    'sale_number' => $this->sale->sale_number,
                    'custom_fields' => [
                        [
                            'display_name' => 'Sale Number',
                            'variable_name' => 'sale_number',
                            'value' => $this->sale->sale_number,
                        ],
                    ],
                ],
            ]);

            if ($response['status']) {
                $this->paymentReference = $response['data']['reference'];
                $this->paymentInitialized = true;

                // Update sale with payment reference
                $this->sale->update([
                    'payment_reference' => $this->paymentReference,
                    'payment_status' => 'pending',
                ]);

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
        return view('livewire.pos.paystack-payment');
    }
}
