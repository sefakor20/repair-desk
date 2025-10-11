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
        'serial_number' => '',
        'imei' => '',
        'notes' => '',
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
            'serial_number' => $device->serial_number ?? '',
            'imei' => $device->imei ?? '',
            'notes' => $device->notes ?? '',
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
            'form.serial_number' => ['nullable', 'string', 'max:255'],
            'form.imei' => ['nullable', 'string', 'max:255'],
            'form.notes' => ['nullable', 'string'],
        ]);

        $this->device->update($validated['form']);

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
