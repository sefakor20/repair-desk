<?php

declare(strict_types=1);

namespace App\Livewire\Devices;

use App\Models\Device;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Device $device;

    public function mount(Device $device): void
    {
        $this->authorize('view', $device);

        $this->device = $device->load(['customer', 'tickets.createdBy']);
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->device);

        $this->device->delete();

        session()->flash('success', 'Device deleted successfully.');

        $this->redirect(route('devices.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.devices.show')->title($this->device->device_name);
    }
}
