<x-mail::message>
    # Reward Successfully Redeemed!

    Hello {{ $customer->first_name }},

    Your reward has been successfully redeemed!

    ## Redemption Details

    <x-mail::panel>
        **Reward**: {{ $reward->name }}
        **Type**: {{ $reward->type->label() }}
        **Value**: @if ($reward->type->value === 'discount')
            {{ $reward->discount_percentage }}% discount
        @else
            ${{ number_format($reward->voucher_amount, 2) }} voucher
        @endif
        **Points Used**: {{ number_format(abs($transaction->points)) }}
        **Remaining Balance**: {{ number_format($transaction->balance_after) }} points
        **Redeemed On**: {{ $transaction->created_at->format('M d, Y \\a\\t h:i A') }}
    </x-mail::panel>

    @if ($reward->description)
        {{ $reward->description }}
    @endif

    ## How to Use Your Reward

    @if ($reward->type->value === 'discount')
        Your {{ $reward->discount_percentage }}% discount will be automatically applied to your next qualifying service.
        Simply mention this reward when you visit us!
    @else
        Your ${{ number_format($reward->voucher_amount, 2) }} voucher code is:
        **REWARD-{{ strtoupper(substr(md5($transaction->id), 0, 8)) }}**

        Present this code during checkout to redeem your voucher.
    @endif

    @if ($reward->expires_at)
        **Important**: This reward expires on {{ $reward->expires_at->format('M d, Y') }}.
    @endif

    @if ($customer->portal_access_token)
        <x-mail::button :url="route('portal.loyalty.rewards', [
            'customer' => $customer->id,
            'token' => $customer->portal_access_token,
        ])">
            Browse More Rewards
        </x-mail::button>
    @endif

    Thank you for being a loyal customer!

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
