<?php

declare(strict_types=1);

namespace App\Livewire\Portal\Auth;

use App\Mail\PortalAccessLink;
use App\Models\Customer;
use Illuminate\Support\Facades\Mail;
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

            $portalUrl = route('portal.loyalty.dashboard', [
                'customer' => $customer->id,
                'token' => $customer->portal_access_token,
            ]);

            Mail::to($customer->email)->send(new PortalAccessLink($customer, $portalUrl));
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
