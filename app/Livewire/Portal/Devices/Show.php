<?php

declare(strict_types=1);

namespace App\Livewire\Portal\Devices;

use App\Models\{Customer, Device};
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal-fullpage')]
class Show extends Component
{
    public Customer $customer;

    public Device $device;

    public function mount(Customer $customer, Device $device): void
    {
        // Ensure device belongs to customer
        if ($device->customer_id !== $customer->id) {
            abort(403, 'Unauthorized access to device');
        }

        $this->customer = $customer;
        $this->device = $device;

        // Ensure customer has a portal access token
        if (! $customer->portal_access_token) {
            $customer->generatePortalAccessToken();
        }
    }

    public function render(): View
    {
        $this->device->load([
            'tickets.assignedTo',
            'tickets.invoice',
        ]);

        return view('livewire.portal.devices.show');
    }
}
