<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Models\ShopSettings;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Shop extends Component
{
    public string $shop_name = '';

    public string $address = '';

    public string $city = '';

    public string $state = '';

    public string $zip = '';

    public string $country = 'USA';

    public string $phone = '';

    public string $email = '';

    public string $website = '';

    public string $tax_rate = '0';

    public string $currency = 'USD';

    public function mount(): void
    {
        $this->authorize('accessSettings', User::class);

        $settings = ShopSettings::getInstance();

        $this->shop_name = $settings->shop_name;
        $this->address = $settings->address ?? '';
        $this->city = $settings->city ?? '';
        $this->state = $settings->state ?? '';
        $this->zip = $settings->zip ?? '';
        $this->country = $settings->country;
        $this->phone = $settings->phone ?? '';
        $this->email = $settings->email ?? '';
        $this->website = $settings->website ?? '';
        $this->tax_rate = (string) $settings->tax_rate;
        $this->currency = $settings->currency;
    }

    public function save(): void
    {
        $this->authorize('accessSettings', User::class);

        $validated = $this->validate([
            'shop_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'zip' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'currency' => ['required', 'string', 'size:3'],
        ]);

        // Convert empty strings to null for optional fields
        foreach (['address', 'city', 'state', 'zip', 'phone', 'email', 'website'] as $field) {
            if (empty($validated[$field])) {
                $validated[$field] = null;
            }
        }

        $settings = ShopSettings::getInstance();
        $settings->update($validated);

        session()->flash('success', 'Shop settings updated successfully.');

        $this->dispatch('settings-saved');
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.settings.shop');
    }
}
