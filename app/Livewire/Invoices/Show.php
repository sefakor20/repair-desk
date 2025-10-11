<?php

declare(strict_types=1);

namespace App\Livewire\Invoices;

use App\Enums\{InvoiceStatus, PaymentMethod};
use App\Models\{Invoice, Payment};
use Illuminate\Support\Facades\{Auth, DB};
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Invoice $invoice;

    public bool $showPaymentModal = false;
    public string $amount = '';
    public string $paymentMethod = '';
    public string $transactionReference = '';
    public string $paymentNotes = '';

    public function mount(Invoice $invoice): void
    {
        $this->authorize('view', $invoice);

        $this->invoice = $invoice->load(['customer', 'ticket.device', 'payments.processedBy']);
    }

    public function render()
    {
        return view('livewire.invoices.show');
    }

    public function openPaymentModal(): void
    {
        $this->authorize('processPayment', $this->invoice);

        $this->resetPaymentForm();
        $this->amount = (string) $this->invoice->balance_due;
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->resetPaymentForm();
    }

    public function recordPayment(): void
    {
        $this->authorize('processPayment', $this->invoice);

        $validated = $this->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $this->invoice->balance_due],
            'paymentMethod' => ['required', 'string', 'in:cash,card,bank_transfer'],
            'transactionReference' => ['nullable', 'string', 'max:255'],
            'paymentNotes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated) {
            $payment = Payment::create([
                'invoice_id' => $this->invoice->id,
                'ticket_id' => $this->invoice->ticket_id,
                'amount' => $validated['amount'],
                'payment_method' => PaymentMethod::from($validated['paymentMethod']),
                'payment_date' => now(),
                'processed_by' => Auth::id(),
                'transaction_reference' => $validated['transactionReference'] ?? null,
                'notes' => $validated['paymentNotes'] ?? null,
            ]);

            // Update invoice status if fully paid
            if ($this->invoice->fresh()->balance_due <= 0) {
                $this->invoice->update(['status' => InvoiceStatus::Paid]);
            }
        });

        $this->invoice->refresh()->load('payments.processedBy');

        $this->closePaymentModal();

        session()->flash('success', 'Payment recorded successfully.');
    }

    private function resetPaymentForm(): void
    {
        $this->reset(['amount', 'paymentMethod', 'transactionReference', 'paymentNotes']);
        $this->resetErrorBag();
    }
}
