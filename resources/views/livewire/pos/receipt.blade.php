<div class="mx-auto max-w-3xl p-8 receipt-container">
    {{-- Print Controls (Hidden on Print) --}}
    <div class="no-print mb-6 flex justify-between gap-2">
        <flux:button variant="ghost" href="{{ route('pos.show', ['sale' => $sale->id]) }}" wire:navigate>
            <flux:icon.arrow-left class="mr-2" />
            Back to Sale
        </flux:button>
        <div class="flex gap-2">
            <flux:button variant="ghost" onclick="window.print()">
                <flux:icon.printer class="mr-2" />
                Print Receipt
            </flux:button>
            <flux:button variant="primary" onclick="window.print()">
                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Print (Thermal)
            </flux:button>
        </div>
    </div>

    {{-- Receipt Content --}}
    <div class="receipt-content border-2 border-zinc-300 bg-white p-8 shadow-lg">
        {{-- Header --}}
        <div class="receipt-header mb-6 border-b-2 border-dashed border-zinc-400 pb-4 text-center">
            <h1 class="text-3xl font-bold uppercase tracking-wide">{{ $settings->shop_name ?? config('app.name') }}</h1>
            @if ($settings->address)
                <p class="mt-2 text-sm text-zinc-700">{{ $settings->address }}</p>
            @endif
            <div class="mt-1 text-sm text-zinc-700">
                @if ($settings->phone)
                    <span>Tel: {{ $settings->phone }}</span>
                @endif
                @if ($settings->phone && $settings->email)
                    <span class="mx-2">|</span>
                @endif
                @if ($settings->email)
                    <span>{{ $settings->email }}</span>
                @endif
            </div>
            @if ($settings->tax_number ?? false)
                <p class="mt-1 text-xs text-zinc-600">TIN: {{ $settings->tax_number }}</p>
            @endif
        </div>

        <div class="receipt-title mb-6 border-y-2 border-double border-zinc-400 py-3">
            <h2 class="text-center text-2xl font-bold uppercase tracking-wider">Sales Receipt</h2>
        </div>

        {{-- Sale Details --}}
        <div class="receipt-details mb-6 space-y-2 border-b-2 border-dashed border-zinc-400 pb-4 text-sm">
            <div class="flex justify-between">
                <span class="font-semibold">Receipt #:</span>
                <span class="font-mono">{{ $sale->sale_number }}</span>
            </div>
            <div class="flex justify-between">
                <span class="font-semibold">Date:</span>
                <span>{{ $sale->sale_date->format('d/m/Y h:i A') }}</span>
            </div>
            @if ($sale->customer)
                <div class="flex justify-between">
                    <span class="font-semibold">Customer:</span>
                    <span>{{ $sale->customer->full_name }}</span>
                </div>
                @if ($sale->customer->phone)
                    <div class="flex justify-between">
                        <span class="font-semibold">Phone:</span>
                        <span>{{ $sale->customer->phone }}</span>
                    </div>
                @endif
            @endif
            <div class="flex justify-between">
                <span class="font-semibold">Served By:</span>
                <span>{{ $sale->soldBy->name }}</span>
            </div>
        </div>

        {{-- Items Table --}}
        <table class="receipt-table mb-6 w-full border-collapse">
            <thead>
                <tr class="border-b-2 border-zinc-800">
                    <th class="py-2 text-left text-xs font-bold uppercase">Item</th>
                    <th class="py-2 text-center text-xs font-bold uppercase">Qty</th>
                    <th class="py-2 text-right text-xs font-bold uppercase">Price</th>
                    <th class="py-2 text-right text-xs font-bold uppercase">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                    <tr class="border-b border-dotted border-zinc-300">
                        <td class="py-2 pr-2 text-sm leading-tight">
                            {{ $item->inventoryItem->name ?? 'Unknown Item' }}
                            @if ($item->inventoryItem->sku ?? false)
                                <div class="text-xs text-zinc-500">SKU: {{ $item->inventoryItem->sku }}</div>
                            @endif
                        </td>
                        <td class="py-2 text-center text-sm font-medium">{{ $item->quantity }}</td>
                        <td class="py-2 text-right text-sm currency-amount">{{ format_currency($item->unit_price) }}
                        </td>
                        <td class="py-2 text-right text-sm font-semibold currency-amount">
                            {{ format_currency($item->subtotal) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="receipt-totals mb-6 border-t-2 border-zinc-800 pt-4">
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span>Subtotal:</span>
                    <span class="currency-amount">{{ format_currency($sale->subtotal) }}</span>
                </div>
                @if ($sale->tax_amount > 0)
                    <div class="flex justify-between text-sm">
                        <span>Tax ({{ number_format($sale->tax_rate, 1) }}%):</span>
                        <span class="currency-amount">{{ format_currency($sale->tax_amount) }}</span>
                    </div>
                @endif
                @if ($sale->discount_amount > 0)
                    <div class="flex justify-between text-sm">
                        <span>Discount:</span>
                        <span
                            class="currency-amount text-green-600">-{{ format_currency($sale->discount_amount) }}</span>
                    </div>
                @endif
            </div>
            <div class="mt-3 flex justify-between border-y-2 border-double border-zinc-800 py-3 text-xl font-bold">
                <span class="uppercase">Total:</span>
                <span class="currency-amount">{{ format_currency($sale->total_amount) }}</span>
            </div>
            <div class="mt-2 flex justify-between text-sm text-zinc-600">
                <span>Items: {{ $sale->items->sum('quantity') }}</span>
                <span>{{ $sale->items->count() }} {{ Str::plural('item', $sale->items->count()) }}</span>
            </div>
        </div>

        {{-- Payment Info --}}
        <div class="receipt-payment mb-6 space-y-2 border-y-2 border-dashed border-zinc-400 py-4">
            <div class="flex justify-between text-sm">
                <span class="font-semibold">Payment Method:</span>
                <span class="font-medium uppercase">{{ $sale->payment_method->label() }}</span>
            </div>
            @if ($sale->payment_reference)
                <div class="flex justify-between text-sm">
                    <span class="font-semibold">Reference:</span>
                    <span class="font-mono text-xs">{{ $sale->payment_reference }}</span>
                </div>
            @endif
            <div class="flex justify-between text-sm">
                <span class="font-semibold">Status:</span>
                <span class="font-medium uppercase text-green-600">{{ $sale->payment_status ?? 'Paid' }}</span>
            </div>
        </div>

        @if ($sale->notes)
            <div class="mb-6 border-t border-zinc-300 pt-4">
                <p class="text-sm font-semibold">Notes:</p>
                <p class="text-sm">{{ $sale->notes }}</p>
            </div>
        @endif

        {{-- Footer --}}
        <div class="receipt-footer mt-8 border-t-2 border-dashed border-zinc-400 pt-4 text-center">
            <p class="thank-you text-lg font-bold uppercase tracking-wide">Thank You!</p>
            <p class="mt-2 text-sm">We appreciate your business</p>
            @if ($settings->website)
                <p class="mt-2 text-sm text-zinc-700">{{ $settings->website }}</p>
            @endif
            @if ($settings->return_policy_days ?? false)
                <p class="mt-3 text-xs text-zinc-600">
                    Returns accepted within {{ $settings->return_policy_days }} days with receipt
                </p>
            @endif
            <div class="mt-4 border-t border-dotted border-zinc-300 pt-3">
                <p class="text-xs text-zinc-500">
                    This is a computer-generated receipt
                </p>
                <p class="mt-1 text-xs text-zinc-400">
                    Printed on {{ now()->format('d/m/Y \a\t h:i A') }}
                </p>
                <p class="mt-3 text-xs font-medium text-zinc-600">
                    Powered by <span class="font-semibold">rCodez</span> • www.rcodez.com
                </p>
            </div>
        </div>
    </div>
</div>
