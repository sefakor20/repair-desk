<?php

declare(strict_types=1);

namespace App\Livewire\Devices;

use App\Models\{Customer, Device};
use Livewire\Attributes\{Layout, Url};
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Devices'])]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $customerFilter = '';

    #[Url]
    public string $typeFilter = '';

    public function delete(Device $device): void
    {
        $this->authorize('delete', $device);

        $device->delete();

        session()->flash('success', 'Device deleted successfully.');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCustomerFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'customerFilter', 'typeFilter']);
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.devices.index', [
            'devices' => Device::query()
                ->with(['customer', 'tickets'])
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('brand', 'like', "%{$this->search}%")
                            ->orWhere('model', 'like', "%{$this->search}%")
                            ->orWhere('serial_number', 'like', "%{$this->search}%")
                            ->orWhere('imei', 'like', "%{$this->search}%")
                            ->orWhereHas('customer', function ($q) {
                                $q->where('first_name', 'like', "%{$this->search}%")
                                    ->orWhere('last_name', 'like', "%{$this->search}%");
                            });
                    });
                })
                ->when($this->customerFilter, function ($query) {
                    $query->where('customer_id', $this->customerFilter);
                })
                ->when($this->typeFilter, function ($query) {
                    $query->where('type', $this->typeFilter);
                })
                ->withCount('tickets')
                ->latest()
                ->paginate(15),
            'customers' => Customer::orderBy('first_name')->get(),
            'types' => Device::query()->distinct()->pluck('type')->sort()->values(),
        ]);
    }
}
