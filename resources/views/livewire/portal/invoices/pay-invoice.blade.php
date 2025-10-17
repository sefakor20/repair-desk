<div>
    <x-layouts.portal-content :customer="$customer" :title="'Pay Invoice #' . $invoice->invoice_number">
        <div class="space-y-6">
            {{-- Back Button --}}
            <div>
                <flux:button
                    href="{{ route('portal.invoices.index', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}"
                    variant="ghost" size="sm">
                    <flux:icon.arrow-left class="w-4 h-4" />
                    Back to Invoices
                </flux:button>
            </div>

            {{-- Invoice Summary --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Invoice Summary</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Invoice Number
                        </h3>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $invoice->invoice_number }}</p>
                    </div>

                    @if ($invoice->ticket)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Related
                                Ticket</h3>
                            <p class="text-gray-900 dark:text-white">{{ $invoice->ticket->ticket_number }}</p>
                            @if ($invoice->ticket->device)
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $invoice->ticket->device->brand }} {{ $invoice->ticket->device->model }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                <flux:separator class="my-6" />

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                        <span class="text-gray-900 dark:text-white">GH₵
                            {{ number_format($invoice->subtotal, 2) }}</span>
                    </div>

                    @if ($invoice->tax_amount > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Tax
                                ({{ number_format($invoice->tax_rate, 1) }}%)</span>
                            <span class="text-gray-900 dark:text-white">GH₵
                                {{ number_format($invoice->tax_amount, 2) }}</span>
                        </div>
                    @endif

                    @if ($invoice->discount > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Discount</span>
                            <span class="text-green-600 dark:text-green-400">- GH₵
                                {{ number_format($invoice->discount, 2) }}</span>
                        </div>
                    @endif

                    <flux:separator />

                    <div class="flex items-center justify-between text-lg font-semibold">
                        <span class="text-gray-900 dark:text-white">Total Amount</span>
                        <span class="text-gray-900 dark:text-white">GH₵ {{ number_format($invoice->total, 2) }}</span>
                    </div>

                    @if ($invoice->total_paid > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Already Paid</span>
                            <span class="text-green-600 dark:text-green-400">GH₵
                                {{ number_format($invoice->total_paid, 2) }}</span>
                        </div>

                        <flux:separator />
                    @endif

                    <div class="flex items-center justify-between text-xl font-bold">
                        <span class="text-gray-900 dark:text-white">Amount to Pay</span>
                        <span class="text-purple-600 dark:text-purple-400">GH₵
                            {{ number_format($invoice->balance_due, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Payment Form --}}
            @if (!$paymentInitialized)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Payment Details</h2>

                    @if ($errorMessage)
                        <div
                            class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <p class="text-red-800 dark:text-red-200">{{ $errorMessage }}</p>
                        </div>
                    @endif

                    <form wire:submit="initializePayment">
                        <div class="space-y-4">
                            <flux:field>
                                <flux:label>Email Address</flux:label>
                                <flux:input wire:model="email" type="email" placeholder="your@email.com" required />
                                <flux:error name="email" />
                                <flux:description>We'll send your payment receipt to this email</flux:description>
                            </flux:field>

                            <div class="flex items-center gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <flux:icon.information-circle
                                    class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0" />
                                <p class="text-sm text-blue-800 dark:text-blue-200">
                                    You will be redirected to Paystack's secure payment page to complete your payment.
                                </p>
                            </div>

                            <div class="flex items-center justify-between pt-4">
                                <flux:button
                                    href="{{ route('portal.invoices.index', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}"
                                    variant="ghost">
                                    Cancel
                                </flux:button>
                                <flux:button type="submit" variant="primary">
                                    <flux:icon.credit-card class="w-5 h-5" />
                                    Pay GH₵ {{ number_format($invoice->balance_due, 2) }}
                                </flux:button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Payment History --}}
            @if ($invoice->payments->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment History</h2>
                    <div class="space-y-3">
                        @foreach ($invoice->payments as $payment)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        GH₵ {{ number_format($payment->amount, 2) }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $payment->payment_date->format('M d, Y - h:i A') }}
                                    </p>
                                    @if ($payment->transaction_reference)
                                        <p class="text-xs text-gray-500 dark:text-gray-500 font-mono">
                                            Ref: {{ $payment->transaction_reference }}
                                        </p>
                                    @endif
                                </div>
                                <flux:badge variant="success">
                                    {{ str($payment->payment_method->value)->title() }}
                                </flux:badge>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </x-layouts.portal-content>
</div>

@script
    <script>
        $wire.on('redirect-to-paystack', (event) => {
            window.location.href = event.url;
        });
    </script>
@endscript
