<x-mail::message>
    # You Earned Loyalty Points!

    Hello {{ $customer->first_name }},

    Great news! You've just earned **{{ number_format($transaction->points) }} loyalty points**.

    ## Transaction Details

    **Description**: {{ $transaction->description }}
    **Points Earned**: +{{ number_format($transaction->points) }}
    **New Balance**: {{ number_format($transaction->balance_after) }} points
    **Date**: {{ $transaction->created_at->format('M d, Y') }}

    @if ($customer->loyaltyAccount && $customer->loyaltyAccount->loyaltyTier)
        You're currently a **{{ $customer->loyaltyAccount->loyaltyTier->name }}** member, enjoying
        **{{ $customer->loyaltyAccount->loyaltyTier->discount_percentage }}% discount** on all services!
    @endif

    @if ($customer->portal_access_token)
        <x-mail::button :url="route('portal.loyalty.dashboard', [
            'customer' => $customer->id,
            'token' => $customer->portal_access_token,
        ])">
            View Your Dashboard
        </x-mail::button>
    @endif

    Keep earning points to unlock amazing rewards and climb to higher tiers!

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
