<?php

declare(strict_types=1);

namespace App\Livewire\Customers;

use App\Livewire\Concerns\WithToast;
use App\Models\Customer;
use Livewire\Attributes\{Layout, Url};
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Customers'])]
class Index extends Component
{
    use WithPagination;
    use WithToast;

    #[Url(as: 'q')]
    public string $search = '';

    public function delete(Customer $customer): void
    {
        $this->authorize('delete', $customer);

        $customer->delete();

        $this->toastSuccess('Customer deleted successfully.');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.customers.index', [
            'customers' => Customer::query()
                ->when($this->search, function ($query): void {
                    $query->where(function ($q): void {
                        $q->where('first_name', 'like', "%{$this->search}%")
                            ->orWhere('last_name', 'like', "%{$this->search}%")
                            ->orWhere('email', 'like', "%{$this->search}%")
                            ->orWhere('phone', 'like', "%{$this->search}%");
                    });
                })
                ->withCount(['devices', 'tickets'])
                ->latest()
                ->paginate(15),
        ])->with('Customer', Customer::class);
    }
}
