<x-mail::message>
    # 🎉 Congratulations! You've Been Upgraded!

    Hello {{ $customer->first_name }},

    Excellent news! Your loyalty and continued support have earned you an upgrade to **{{ $newTier->name }}** tier!

    ## Your New Benefits

    @if ($previousTier)
        <x-mail::panel>
            **Previous Tier**: {{ $previousTier->name }} ({{ $previousTier->discount_percentage }}% discount)
            **New Tier**: {{ $newTier->name }} ({{ $newTier->discount_percentage }}% discount)
            **Upgrade Bonus**: {{ $newTier->discount_percentage - $previousTier->discount_percentage }}% additional
            discount!
        </x-mail::panel>
    @else
        <x-mail::panel>
            **Your Tier**: {{ $newTier->name }}
            **Discount**: {{ $newTier->discount_percentage }}% on all services
            **Minimum Points**: {{ number_format($newTier->min_points) }}
        </x-mail::panel>
    @endif

    As a {{ $newTier->name }} member, you now enjoy:
    - **{{ $newTier->discount_percentage }}% discount** on all services
    - Access to exclusive {{ $newTier->name }}-tier rewards
    - Priority support
    - Special promotional offers

    ## Your Current Status

    **Total Points**: {{ number_format($account->total_points) }}
    **Lifetime Points**: {{ number_format($account->lifetime_points) }}
    **Current Tier**: {{ $newTier->name }}

    @if ($customer->portal_access_token)
        <x-mail::button :url="route('portal.loyalty.dashboard', [
            'customer' => $customer->id,
            'token' => $customer->portal_access_token,
        ])">
            View Your Dashboard
        </x-mail::button>
    @endif

    Thank you for being a valued customer! We look forward to continuing to serve you.

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
