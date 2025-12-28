<?php

declare(strict_types=1);

namespace App\Livewire\Devices;

use App\Models\{Customer, Device};
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Register Device'])]
class Create extends Component
{
    public array $form = [
        'customer_id' => '',
        'type' => '',
        'brand' => '',
        'model' => '',
        'color' => '',
        'storage_capacity' => '',
        'serial_number' => '',
        'imei' => '',
        'notes' => '',
        'condition' => '',
        'condition_notes' => '',
        'purchase_date' => '',
        'warranty_expiry' => '',
        'warranty_provider' => '',
        'warranty_notes' => '',
        'password_pin' => '',
    ];

    public function mount(): void
    {
        $this->authorize('create', Device::class);
    }

    public function save(): void
    {
        $this->authorize('create', Device::class);

        $validated = $this->validate([
            'form.customer_id' => ['required', 'exists:customers,id'],
            'form.type' => ['required', 'string', 'max:255'],
            'form.brand' => ['required', 'string', 'max:255'],
            'form.model' => ['required', 'string', 'max:255'],
            'form.color' => ['nullable', 'string', 'max:255'],
            'form.storage_capacity' => ['nullable', 'string', 'max:255'],
            'form.serial_number' => ['nullable', 'string', 'max:255'],
            'form.imei' => ['nullable', 'string', 'max:255'],
            'form.notes' => ['nullable', 'string'],
            'form.condition' => ['nullable', 'string', 'in:excellent,good,fair,poor,damaged'],
            'form.condition_notes' => ['nullable', 'string'],
            'form.purchase_date' => ['nullable', 'date'],
            'form.warranty_expiry' => ['nullable', 'date', 'after:form.purchase_date'],
            'form.warranty_provider' => ['nullable', 'string', 'max:255'],
            'form.warranty_notes' => ['nullable', 'string'],
            'form.password_pin' => ['nullable', 'string', 'max:255'],
        ]);

        // Convert empty strings to null for proper enum handling
        $data = array_map(fn($value) => $value === '' ? null : $value, $validated['form']);

        $device = Device::create($data);

        session()->flash('success', 'Device registered successfully.');

        $this->redirect(route('devices.show', $device), navigate: true);
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.devices.create', [
            'customers' => Customer::orderBy('first_name')->get(),
        ]);
    }
}
