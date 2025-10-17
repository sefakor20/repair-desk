<?php

declare(strict_types=1);

namespace App\Livewire\Portal\Devices;

use App\Models\Customer;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\{Attributes\Url, Component};
use Livewire\WithPagination;

#[Layout('components.layouts.portal-fullpage')]
class Index extends Component
{
    use WithPagination;

    public Customer $customer;

    #[Url(as: 'search')]
    public string $search = '';

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;

        // Ensure customer has a portal access token
        if (! $customer->portal_access_token) {
            $customer->generatePortalAccessToken();
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function render(): View
    {
        $devices = $this->customer->devices()
            ->withCount('tickets')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('brand', 'like', "%{$this->search}%")
                        ->orWhere('model', 'like', "%{$this->search}%")
                        ->orWhere('type', 'like', "%{$this->search}%")
                        ->orWhere('serial_number', 'like', "%{$this->search}%")
                        ->orWhere('imei', 'like', "%{$this->search}%");
                });
            })
            ->latest()
            ->paginate(12);

        return view('livewire.portal.devices.index', [
            'devices' => $devices,
        ]);
    }
}
