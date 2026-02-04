<?php

declare(strict_types=1);

namespace App\Livewire\Tickets;

use App\Enums\{TicketPriority, TicketStatus};
use App\Models\{Customer, Device, Ticket, User};
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    public Ticket $ticket;

    public ?string $selectedCustomerId = null;

    public ?string $selectedDeviceId = null;

    public array $form = [
        'customer_id' => '',
        'device_id' => '',
        'problem_description' => '',
        'diagnosis' => '',
        'status' => '',
        'priority' => '',
        'assigned_to' => '',
        'estimated_completion' => '',
        'actual_completion' => '',
    ];

    public function mount(Ticket $ticket): void
    {
        $this->authorize('update', $ticket);

        $this->ticket = $ticket;
        $this->selectedCustomerId = $ticket->customer_id;
        $this->selectedDeviceId = $ticket->device_id;

        $this->form = [
            'customer_id' => $ticket->customer_id,
            'device_id' => $ticket->device_id,
            'problem_description' => $ticket->problem_description,
            'diagnosis' => $ticket->diagnosis ?? '',
            'status' => $ticket->status->value,
            'priority' => $ticket->priority->value,
            'assigned_to' => $ticket->assigned_to ?? '',
            'estimated_completion' => $ticket->estimated_completion?->format('Y-m-d') ?? '',
            'actual_completion' => $ticket->actual_completion?->format('Y-m-d') ?? '',
        ];
    }

    public function rules(): array
    {
        return [
            'form.customer_id' => 'required|exists:customers,id',
            'form.device_id' => 'required|exists:devices,id',
            'form.problem_description' => 'required|string|min:10|max:1000',
            'form.diagnosis' => 'nullable|string|max:1000',
            'form.status' => 'required|in:new,in_progress,waiting_for_parts,completed,delivered',
            'form.priority' => 'required|in:low,normal,high,urgent',
            'form.assigned_to' => 'nullable|exists:users,id',
            'form.estimated_completion' => 'nullable|date',
            'form.actual_completion' => 'nullable|date',
        ];
    }

    public function updatedSelectedCustomerId(): void
    {
        $this->form['customer_id'] = $this->selectedCustomerId;
        $this->form['device_id'] = '';
        $this->selectedDeviceId = null;
    }

    public function updatedSelectedDeviceId(): void
    {
        $this->form['device_id'] = $this->selectedDeviceId;
    }

    public function update(): void
    {
        $this->authorize('update', $this->ticket);

        $validated = $this->validate();

        $this->ticket->update([
            'customer_id' => $validated['form']['customer_id'],
            'device_id' => $validated['form']['device_id'],
            'problem_description' => $validated['form']['problem_description'],
            'diagnosis' => $validated['form']['diagnosis'] ?: null,
            'status' => $validated['form']['status'],
            'priority' => $validated['form']['priority'],
            'assigned_to' => $validated['form']['assigned_to'] ?: null,
            'estimated_completion' => $validated['form']['estimated_completion'] ?: null,
            'actual_completion' => $validated['form']['actual_completion'] ?: null,
        ]);

        session()->flash('success', 'Ticket updated successfully.');

        $this->redirect(route('tickets.show', $this->ticket), navigate: true);
    }

    public function render(): View
    {
        $customers = Customer::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $devices = $this->selectedCustomerId
            ? Device::where('customer_id', $this->selectedCustomerId)->get()
            : collect();

        $technicians = User::whereIn('role', ['admin', 'manager', 'technician'])
            ->when(
                auth()->user()->branch_id,
                fn($query) => $query->where('branch_id', auth()->user()->branch_id),
            )
            ->orderBy('name')
            ->get();

        $priorities = TicketPriority::cases();
        $statuses = TicketStatus::cases();

        return view('livewire.tickets.edit', [
            'title' => "Edit Ticket #{$this->ticket->ticket_number}",
            'customers' => $customers,
            'devices' => $devices,
            'technicians' => $technicians,
            'priorities' => $priorities,
            'statuses' => $statuses,
        ]);
    }
}
