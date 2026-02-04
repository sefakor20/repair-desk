<?php

declare(strict_types=1);

namespace App\Livewire\Tickets;

use App\Enums\{TicketPriority, TicketStatus};
use App\Models\{Customer, Device, Ticket, User};
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'New Ticket'])]
class Create extends Component
{
    public ?string $selectedCustomerId = null;

    public ?string $selectedDeviceId = null;

    public array $form = [
        'customer_id' => '',
        'device_id' => '',
        'problem_description' => '',
        'diagnosis' => '',
        'priority' => '',
        'assigned_to' => '',
        'estimated_completion' => '',
    ];

    public function rules(): array
    {
        return [
            'form.customer_id' => 'required|exists:customers,id',
            'form.device_id' => 'required|exists:devices,id',
            'form.problem_description' => 'required|string|min:10|max:1000',
            'form.diagnosis' => 'nullable|string|max:1000',
            'form.priority' => 'required|in:low,normal,high,urgent',
            'form.assigned_to' => 'nullable|exists:users,id',
            'form.estimated_completion' => 'nullable|date|after:today',
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

    public function save(): void
    {
        $this->authorize('create', Ticket::class);

        $validated = $this->validate();

        $ticket = Ticket::create([
            'customer_id' => $validated['form']['customer_id'],
            'device_id' => $validated['form']['device_id'],
            'problem_description' => $validated['form']['problem_description'],
            'diagnosis' => $validated['form']['diagnosis'] ?: null,
            'priority' => $validated['form']['priority'],
            'assigned_to' => $validated['form']['assigned_to'] ?: null,
            'estimated_completion' => $validated['form']['estimated_completion'] ?: null,
            'status' => TicketStatus::New,
            'created_by' => Auth::id(),
            'branch_id' => auth()->user()->branch_id,
        ]);

        session()->flash('success', 'Ticket created successfully.');

        $this->redirect(route('tickets.show', $ticket), navigate: true);
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

        return view('livewire.tickets.create', [
            'customers' => $customers,
            'devices' => $devices,
            'technicians' => $technicians,
            'priorities' => $priorities,
        ]);
    }
}
