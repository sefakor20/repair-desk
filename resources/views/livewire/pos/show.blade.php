<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" class="mb-2">{{ __('Sale Details') }}</flux:heading>
                <flux:text>{{ $sale->sale_number }}</flux:text>
            </div>
            <div class="flex gap-2">
                <flux:button href="{{ route('pos.receipt', $sale) }}" wire:navigate variant="primary">
                    <flux:icon.printer class="mr-2" />
                    {{ __('Print Receipt') }}
                </flux:button>
                @can('refund', $sale)
                    @if ($sale->status === App\Enums\PosSaleStatus::Completed)
                        <flux:button variant="danger" wire:click="openRefundModal">
                            {{ __('Refund Sale') }}
                        </flux:button>
                    @endif
                @endcan
                <flux:button href="{{ route('pos.index') }}" wire:navigate variant="ghost">
                    {{ __('Back to Sales') }}
                </flux:button>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Sale Information --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Items Sold --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">{{ __('Items Sold') }}</flux:heading>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-zinc-200 dark:border-zinc-700">
                            <tr>
                                <th
                                    class="pb-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Product') }}
                                </th>
                                <th
                                    class="pb-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Qty') }}
                                </th>
                                <th
                                    class="pb-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Price') }}
                                </th>
                                <th
                                    class="pb-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Subtotal') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($sale->items as $item)
                                <tr>
                                    <td class="py-3">
                                        <flux:text class="font-medium">{{ $item->inventoryItem->name }}</flux:text>
                                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $item->inventoryItem->sku }}
                                        </flux:text>
                                    </td>
                                    <td class="py-3 text-right">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="py-3 text-right">
                                        {{ format_currency($item->unit_price) }}
                                    </td>
                                    <td class="py-3 text-right font-medium">
                                        {{ format_currency($item->subtotal) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Sale Notes --}}
            @if ($sale->notes)
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:heading size="lg" class="mb-3">{{ __('Notes') }}</flux:heading>
                    <flux:text class="whitespace-pre-wrap">{{ $sale->notes }}</flux:text>
                </div>
            @endif
        </div>

        {{-- Sale Summary - Right Side --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Status and Customer --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="mb-4">
                    <flux:text class="mb-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</flux:text>
                    <flux:badge :color="$sale->status->color()" size="lg">
                        {{ $sale->status->label() }}
                    </flux:badge>
                </div>

                <div class="mb-4">
                    <flux:text class="mb-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Customer') }}</flux:text>
                    <flux:text class="font-medium">
                        {{ $sale->customer ? $sale->customer->full_name : __('Walk-in Customer') }}
                    </flux:text>
                </div>

                <div class="mb-4">
                    <flux:text class="mb-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Sale Date') }}</flux:text>
                    <flux:text>{{ $sale->sale_date->format('M d, Y \a\t H:i') }}</flux:text>
                </div>

                <div>
                    <flux:text class="mb-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Sold By') }}</flux:text>
                    <flux:text>{{ $sale->soldBy->name }}</flux:text>
                </div>
            </div>

            {{-- Payment Information --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">{{ __('Payment') }}</flux:heading>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <flux:text>{{ __('Subtotal') }}</flux:text>
                        <flux:text class="font-medium">{{ format_currency($sale->subtotal) }}</flux:text>
                    </div>

                    @if ($sale->discount_amount > 0)
                        <div class="flex justify-between">
                            <flux:text>{{ __('Discount') }}</flux:text>
                            <flux:text class="font-medium text-red-600 dark:text-red-400">
                                -{{ format_currency($sale->discount_amount) }}
                            </flux:text>
                        </div>
                    @endif

                    <div class="flex justify-between">
                        <flux:text>{{ __('Tax') }} ({{ number_format($sale->tax_rate, 2) }}%)</flux:text>
                        <flux:text class="font-medium">{{ format_currency($sale->tax_amount) }}</flux:text>
                    </div>

                    <div class="flex justify-between border-t border-zinc-200 pt-3 dark:border-zinc-700">
                        <flux:heading size="lg">{{ __('Total') }}</flux:heading>
                        <flux:heading size="lg" class="text-green-600 dark:text-green-400">
                            {{ format_currency($sale->total_amount) }}
                        </flux:heading>
                    </div>

                    <div class="border-t border-zinc-200 pt-3 dark:border-zinc-700">
                        <flux:text class="mb-1 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Payment Method') }}
                        </flux:text>
                        <flux:badge size="lg">{{ $sale->payment_method->label() }}</flux:badge>
                    </div>

                    @if ($sale->payment_status)
                        <div class="border-t border-zinc-200 pt-3 dark:border-zinc-700">
                            <flux:text class="mb-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ __('Payment Status') }}
                            </flux:text>
                            <flux:badge
                                :color="$sale->payment_status === 'completed' ? 'green' : ($sale->payment_status === 'failed' ? 'red' : 'yellow')"
                                size="lg">
                                {{ ucfirst($sale->payment_status) }}
                            </flux:badge>

                            @if ($sale->payment_status === 'pending' && $sale->payment_method === App\Enums\PaymentMethod::Card)
                                <flux:button href="{{ route('pos.paystack', $sale) }}" wire:navigate variant="primary"
                                    class="mt-3 w-full">
                                    <flux:icon.credit-card class="mr-2" />
                                    {{ __('Complete Payment') }}
                                </flux:button>
                            @endif
                        </div>
                    @endif

                    @if ($sale->payment_reference)
                        <div class="border-t border-zinc-200 pt-3 dark:border-zinc-700">
                            <flux:text class="mb-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ __('Payment Reference') }}
                            </flux:text>
                            <flux:text class="font-mono text-xs">{{ $sale->payment_reference }}</flux:text>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Refund Modal --}}
    <flux:modal :open="$showRefundModal" @close="closeRefundModal">
        <div class="space-y-6 p-6">
            <div>
                <flux:heading size="lg">{{ __('Refund Sale') }}</flux:heading>
                <flux:subheading>
                    {{ __('This will refund the sale and restore inventory quantities.') }}
                </flux:subheading>
            </div>

            <form wire:submit="processRefund">
                <flux:field>
                    <flux:label>{{ __('Refund Reason') }}</flux:label>
                    <flux:textarea wire:model="refundReason" rows="3" maxlength="500" required />
                    @error('refundReason')
                        <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                    @enderror
                </flux:field>

                @error('refund')
                    <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                @enderror

                <div class="mt-6 flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="closeRefundModal">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="danger">
                        {{ __('Process Refund') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
