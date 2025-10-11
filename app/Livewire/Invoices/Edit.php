<?php

declare(strict_types=1);

namespace App\Livewire\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    public Invoice $invoice;

    public string $subtotal = '';
    public string $taxRate = '';
    public string $discount = '';
    public string $notes = '';
    public InvoiceStatus $status;

    public function mount(Invoice $invoice): void
    {
        $this->authorize('update', $invoice);

        $this->invoice = $invoice;
        $this->subtotal = (string) $invoice->subtotal;
        $this->taxRate = (string) $invoice->tax_rate;
        $this->discount = (string) $invoice->discount;
        $this->notes = (string) $invoice->notes;
        $this->status = $invoice->status;
    }

    public function render()
    {
        return view('livewire.invoices.edit');
    }

    public function update(): void
    {
        $this->authorize('update', $this->invoice);

        $validated = $this->validate([
            'subtotal' => ['required', 'numeric', 'min:0'],
            'taxRate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['required'],
        ]);

        $subtotal = (float) $validated['subtotal'];
        $taxRate = isset($validated['taxRate']) && $validated['taxRate'] !== '' ? (float) $validated['taxRate'] : 0;
        $discount = isset($validated['discount']) && $validated['discount'] !== '' ? (float) $validated['discount'] : 0;

        $taxAmount = ($subtotal - $discount) * ($taxRate / 100);
        $total = $subtotal - $discount + $taxAmount;

        $this->invoice->update([
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'discount' => $discount,
            'total' => $total,
            'notes' => $validated['notes'] ?? null,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Invoice updated successfully.');

        $this->redirect(route('invoices.show', $this->invoice), navigate: true);
    }
}
