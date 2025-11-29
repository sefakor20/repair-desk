<?php

declare(strict_types=1);

namespace App\Livewire\Devices;

use App\Models\{Customer, Device};
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    public Device $device;

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

    public function mount(Device $device): void
    {
        $this->authorize('update', $device);

        $this->device = $device;

        $this->form = [
            'customer_id' => $device->customer_id,
            'type' => $device->type,
            'brand' => $device->brand,
            'model' => $device->model,
            'color' => $device->color ?? '',
            'storage_capacity' => $device->storage_capacity ?? '',
            'serial_number' => $device->serial_number ?? '',
            'imei' => $device->imei ?? '',
            'notes' => $device->notes ?? '',
            'condition' => $device->condition?->value ?? '',
            'condition_notes' => $device->condition_notes ?? '',
            'purchase_date' => $device->purchase_date?->format('Y-m-d') ?? '',
            'warranty_expiry' => $device->warranty_expiry?->format('Y-m-d') ?? '',
            'warranty_provider' => $device->warranty_provider ?? '',
            'warranty_notes' => $device->warranty_notes ?? '',
            'password_pin' => '', // Don't populate password for security
        ];
    }

    public function save(): void
    {
        $this->authorize('update', $this->device);

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
        $updateData = array_map(fn($value) => $value === '' ? null : $value, $validated['form']);

        // Only update password if provided
        if (empty($updateData['password_pin'])) {
            unset($updateData['password_pin']);
        }

        $this->device->update($updateData);

        session()->flash('success', 'Device updated successfully.');

        $this->redirect(route('devices.show', $this->device), navigate: true);
    }

    public function render()
    {
        return view('livewire.devices.edit', [
            'customers' => Customer::orderBy('first_name')->get(),
        ])->title('Edit ' . $this->device->device_name);
    }
}
