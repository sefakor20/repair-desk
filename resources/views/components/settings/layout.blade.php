<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('settings.profile')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.password')" wire:navigate>{{ __('Password') }}</flux:navlist.item>
            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <flux:navlist.item :href="route('two-factor.show')" wire:navigate>{{ __('Two-Factor Auth') }}
                </flux:navlist.item>
            @endif
            <flux:navlist.item :href="route('settings.appearance')" wire:navigate>{{ __('Appearance') }}
            </flux:navlist.item>
            @can('accessSettings', App\Models\User::class)
                <flux:navlist.group heading="Shop Settings" class="mt-6">
                    <flux:navlist.item :href="route('settings.shop')" :current="request()->routeIs('settings.shop')"
                        icon="building-storefront">
                        Shop Profile
                    </flux:navlist.item>
                    <flux:navlist.item :href="route('settings.return-policies')"
                        :current="request()->routeIs('settings.return-policies')" icon="document-text">
                        Return Policies
                    </flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group heading="Loyalty Program" class="mt-6">
                    <flux:navlist.item :href="route('settings.loyalty-tiers')"
                        :current="request()->routeIs('settings.loyalty-tiers')" icon="trophy">
                        Loyalty Tiers
                    </flux:navlist.item>
                    <flux:navlist.item :href="route('settings.loyalty-rewards')"
                        :current="request()->routeIs('settings.loyalty-rewards')" icon="gift">
                        Loyalty Rewards
                    </flux:navlist.item>
                </flux:navlist.group>
            @endcan
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
