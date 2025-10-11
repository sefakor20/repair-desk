<div>
    <flux:heading size="lg" class="mb-6">Complete Payment with Paystack</flux:heading>

    @if ($errorMessage)
        <flux:callout variant="danger" class="mb-4">
            {{ $errorMessage }}
        </flux:callout>
    @endif

    <div class="mb-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
        <div class="space-y-4">
            <div>
                <flux:text class="font-semibold">Sale Number:</flux:text>
                <flux:text>{{ $sale->sale_number }}</flux:text>
            </div>

            <div>
                <flux:text class="font-semibold">Amount to Pay:</flux:text>
                <flux:text class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                    {{ config('paystack.currency') }} {{ number_format($sale->total_amount, 2) }}
                </flux:text>
            </div>

            @if (!$paymentInitialized)
                <flux:field>
                    <flux:label>Email Address</flux:label>
                    <flux:input wire:model="email" type="email" placeholder="customer@example.com" />
                    @error('email')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <div class="flex gap-4 pt-4">
                    <flux:button variant="primary" wire:click="initializePayment" class="flex-1">
                        <flux:icon.credit-card class="mr-2" />
                        Pay with Paystack
                    </flux:button>

                    <flux:button variant="ghost" href="{{ route('pos.show', ['sale' => $sale->id]) }}" wire:navigate>
                        Cancel
                    </flux:button>
                </div>
            @else
                <div class="py-8 text-center">
                    <flux:icon.arrow-path class="mx-auto mb-4 animate-spin" size="lg" />
                    <flux:text>Redirecting to Paystack payment page...</flux:text>
                </div>
            @endif
        </div>
    </div>

    @script
        <script>
            $wire.on('redirect-to-paystack', (event) => {
                window.location.href = event.url;
            });
        </script>
    @endscript
</div>
