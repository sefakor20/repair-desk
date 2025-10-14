<x-mail::message>
    # Welcome to Your Customer Portal!

    Hello {{ $customer->first_name }},

    We're excited to provide you with access to your personal customer portal. Here you can:

    - View your loyalty points and tier status
    - Browse and redeem exclusive rewards
    - Track your transaction history
    - Monitor your repair tickets

    <x-mail::button :url="$portalUrl">
        Access Your Portal
    </x-mail::button>

    This link will remain active for 30 days. If you need a new access link, simply visit our portal login page and
    request one.

    **Important**: Keep this link secure and don't share it with anyone else, as it provides direct access to your
    account information.

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
