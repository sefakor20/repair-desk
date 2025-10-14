<?php

declare(strict_types=1);

namespace App\Livewire\Portal\Profile;

use App\Models\Customer;
use Illuminate\View\View;
use Livewire\{Attributes\Validate, Component};

class Edit extends Component
{
    public Customer $customer;

    #[Validate('required|string|max:255')]
    public string $first_name = '';

    #[Validate('required|string|max:255')]
    public string $last_name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('required|string|max:20')]
    public string $phone = '';

    #[Validate('nullable|string|max:500')]
    public string $address = '';

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;

        // Populate form with existing data
        $this->first_name = $customer->first_name;
        $this->last_name = $customer->last_name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->address = $customer->address ?? '';
    }

    public function save(): void
    {
        $validated = $this->validate();

        // Check if email is unique (except for current customer)
        $this->validate([
            'email' => 'unique:customers,email,' . $this->customer->id,
        ]);

        $this->customer->update($validated);

        $this->dispatch(
            'toast',
            type: 'success',
            message: 'Profile updated successfully!',
        );
    }

    public function render(): View
    {
        return view('livewire.portal.profile.edit');
    }
}
