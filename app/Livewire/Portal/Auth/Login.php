<?php

declare(strict_types=1);

namespace App\Livewire\Portal\Auth;

use App\Models\Customer;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Login extends Component
{
    #[Validate('required|email|exists:customers,email')]
    public string $email = '';

    public bool $linkSent = false;

    public function sendAccessLink(): void
    {
        $this->validate();

        $customer = Customer::where('email', $this->email)->first();

        if ($customer) {
            $customer->generatePortalAccessToken();

            // TODO: Send email with portal URL
            // Mail::to($customer->email)->send(new PortalAccessLink($customer));
        }

        // Always show success to prevent email enumeration
        $this->linkSent = true;
        $this->email = '';
    }

    public function render()
    {
        return view('livewire.portal.auth.login')
            ->layout('components.layouts.guest');
    }
}
