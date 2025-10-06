<?php

declare(strict_types=1);

namespace App\Livewire\Customers;

use App\Models\Customer;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Customer Details'])]
class Show extends Component
{
    public Customer $customer;

    public function mount(Customer $customer): void
    {
        $this->customer = Customer::with(['devices.tickets', 'tickets.invoice'])->find($customer->id);
        $this->authorize('view', $this->customer);
    }

    public function deleteCustomer(): void
    {
        $this->authorize('delete', $this->customer);

        $this->customer->delete();

        session()->flash('success', 'Customer deleted successfully.');

        $this->redirect(route('customers.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.customers.show', [
            'customer' => $this->customer,
        ]);
    }
}
