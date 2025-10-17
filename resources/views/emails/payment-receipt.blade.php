<x-mail::message>
    # Payment Received - Thank You!

    Hello {{ $payment->invoice->customer->first_name }},

    Thank you for your payment! We've successfully received your payment of **GH₵
    {{ number_format($payment->amount, 2) }}**.

    ## Payment Details

    - **Amount Paid**: GH₵ {{ number_format($payment->amount, 2) }}
    - **Payment Method**: {{ $payment->payment_method->value }}
    - **Transaction Reference**: {{ $payment->transaction_reference }}
    - **Date**: {{ $payment->created_at->format('F d, Y h:i A') }}

    ## Invoice Summary

    - **Invoice Number**: {{ $payment->invoice->invoice_number }}
    @if ($payment->invoice->ticket)
        - **Ticket Number**: {{ $payment->invoice->ticket->ticket_number }}
        - **Device**: {{ $payment->invoice->ticket->device->brand }} {{ $payment->invoice->ticket->device->model }}
    @endif
    - **Invoice Total**: GH₵ {{ number_format($payment->invoice->total, 2) }}
    - **Amount Paid**: GH₵ {{ number_format($payment->invoice->total_paid, 2) }}
    - **Balance Due**: GH₵ {{ number_format($payment->invoice->balance_due, 2) }}

    Your detailed payment receipt is attached to this email as a PDF.

    @if ($payment->invoice->balance_due > 0)
        <x-mail::panel>
            **Remaining Balance**: GH₵ {{ number_format($payment->invoice->balance_due, 2) }}

            You can view your invoice and make additional payments through your customer portal.
        </x-mail::panel>
    @else
        <x-mail::panel>
            ✓ **Invoice Fully Paid**

            Your invoice has been paid in full. Thank you for your business!
        </x-mail::panel>
    @endif

    @if ($payment->invoice->ticket)
        <x-mail::button :url="route('portal.tickets.show', [
            'customer' => $payment->invoice->customer->id,
            'token' => $payment->invoice->customer->portal_access_token,
            'ticket' => $payment->invoice->ticket->id,
        ])">
            View Ticket Details
        </x-mail::button>
    @endif

    If you have any questions about this payment or your invoice, please don't hesitate to contact us.

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
