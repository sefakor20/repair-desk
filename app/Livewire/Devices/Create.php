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
        'serial_number' => '',
        'imei' => '',
        'notes' => '',
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
            'form.serial_number' => ['nullable', 'string', 'max:255'],
            'form.imei' => ['nullable', 'string', 'max:255'],
            'form.notes' => ['nullable', 'string'],
        ]);

        $device = Device::create($validated['form']);

        session()->flash('success', 'Device registered successfully.');

        $this->redirect(route('devices.show', $device), navigate: true);
    }

    public function render()
    {
        return view('livewire.devices.create', [
            'customers' => Customer::orderBy('first_name')->get(),
        ]);
    }
}
