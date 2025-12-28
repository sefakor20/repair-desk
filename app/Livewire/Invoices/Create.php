<?php

declare(strict_types=1);

namespace App\Livewire\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\{Invoice, Ticket};
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public string $ticketId = '';
    public string $subtotal = '';
    public string $taxRate = '';
    public string $discount = '';
    public string $notes = '';

    public function mount(): void
    {
        $this->authorize('create', Invoice::class);

        // Pre-fill ticket from query parameter
        if (request()->has('ticket')) {
            $this->ticketId = request()->query('ticket');
        }
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $tickets = Ticket::with(['customer', 'device'])
            ->whereDoesntHave('invoice')
            ->latest()
            ->get();

        return view('livewire.invoices.create', [
            'tickets' => $tickets,
        ]);
    }

    public function create(): void
    {
        $this->authorize('create', Invoice::class);

        $validated = $this->validate([
            'ticketId' => ['required', 'exists:tickets,id'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'taxRate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $ticket = Ticket::with('customer')->findOrFail($validated['ticketId']);

        if ($ticket->invoice()->exists()) {
            $this->addError('ticketId', 'This ticket already has an invoice.');
            return;
        }

        $subtotal = (float) $validated['subtotal'];
        $taxRate = isset($validated['taxRate']) ? (float) $validated['taxRate'] : 0;
        $discount = isset($validated['discount']) ? (float) $validated['discount'] : 0;

        $taxAmount = ($subtotal - $discount) * ($taxRate / 100);
        $total = $subtotal - $discount + $taxAmount;

        DB::transaction(function () use ($ticket, $subtotal, $taxRate, $taxAmount, $discount, $total, $validated): void {
            Invoice::create([
                'ticket_id' => $ticket->id,
                'customer_id' => $ticket->customer_id,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'discount' => $discount,
                'total' => $total,
                'status' => InvoiceStatus::Pending,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        session()->flash('success', 'Invoice created successfully.');

        $this->redirect(route('invoices.index'), navigate: true);
    }
}
