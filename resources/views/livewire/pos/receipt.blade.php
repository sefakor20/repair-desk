<div class="mx-auto max-w-3xl p-8">
    {{-- Print Button --}}
    <div class="no-print mb-4 flex justify-end gap-2">
        <flux:button variant="ghost" href="{{ route('pos.show', ['sale' => $sale->id]) }}" wire:navigate>
            <flux:icon.arrow-left class="mr-2" />
            Back to Sale
        </flux:button>
        <flux:button variant="primary" onclick="window.print()">
            <flux:icon.printer class="mr-2" />
            Print Receipt
        </flux:button>
    </div>

    {{-- Receipt Content --}}
    <div class="border-2 border-zinc-300 bg-white p-8">
        {{-- Header --}}
        <div class="mb-6 text-center">
            <h1 class="text-3xl font-bold">{{ $settings->shop_name ?? config('app.name') }}</h1>
            @if ($settings->address)
                <p class="text-sm text-zinc-600">{{ $settings->address }}</p>
            @endif
            @if ($settings->phone)
                <p class="text-sm text-zinc-600">{{ $settings->phone }}</p>
            @endif
            @if ($settings->email)
                <p class="text-sm text-zinc-600">{{ $settings->email }}</p>
            @endif
        </div>

        <div class="mb-6 border-t-2 border-b-2 border-zinc-300 py-4">
            <h2 class="text-center text-xl font-bold">SALES RECEIPT</h2>
        </div>

        {{-- Sale Details --}}
        <div class="mb-6 grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm font-semibold">Receipt #:</p>
                <p class="text-sm">{{ $sale->sale_number }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-semibold">Date:</p>
                <p class="text-sm">{{ $sale->sale_date->format('M d, Y h:i A') }}</p>
            </div>
            @if ($sale->customer)
                <div>
                    <p class="text-sm font-semibold">Customer:</p>
                    <p class="text-sm">{{ $sale->customer->full_name }}</p>
                    @if ($sale->customer->phone)
                        <p class="text-sm">{{ $sale->customer->phone }}</p>
                    @endif
                </div>
            @endif
            <div class="text-right">
                <p class="text-sm font-semibold">Served By:</p>
                <p class="text-sm">{{ $sale->soldBy->name }}</p>
            </div>
        </div>

        {{-- Items Table --}}
        <table class="mb-6 w-full border-collapse">
            <thead>
                <tr class="border-b-2 border-zinc-300">
                    <th class="py-2 text-left text-sm font-semibold">Item</th>
                    <th class="py-2 text-center text-sm font-semibold">Qty</th>
                    <th class="py-2 text-right text-sm font-semibold">Price</th>
                    <th class="py-2 text-right text-sm font-semibold">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                    <tr class="border-b border-zinc-200">
                        <td class="py-2 text-sm">{{ $item->inventoryItem->name ?? 'Unknown Item' }}</td>
                        <td class="py-2 text-center text-sm">{{ $item->quantity }}</td>
                        <td class="py-2 text-right text-sm">{{ format_currency($item->unit_price) }}</td>
                        <td class="py-2 text-right text-sm">{{ format_currency($item->subtotal) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="mb-6 ml-auto max-w-xs space-y-2">
            <div class="flex justify-between text-sm">
                <span>Subtotal:</span>
                <span>{{ format_currency($sale->subtotal) }}</span>
            </div>
            @if ($sale->tax_amount > 0)
                <div class="flex justify-between text-sm">
                    <span>Tax ({{ $sale->tax_rate }}%):</span>
                    <span>{{ format_currency($sale->tax_amount) }}</span>
                </div>
            @endif
            @if ($sale->discount_amount > 0)
                <div class="flex justify-between text-sm text-green-600">
                    <span>Discount:</span>
                    <span>-{{ format_currency($sale->discount_amount) }}</span>
                </div>
            @endif
            <div class="flex justify-between border-t-2 border-zinc-300 pt-2 text-lg font-bold">
                <span>Total:</span>
                <span>{{ format_currency($sale->total_amount) }}</span>
            </div>
        </div>

        {{-- Payment Info --}}
        <div class="mb-6 border-t-2 border-zinc-300 pt-4">
            <div class="flex justify-between text-sm">
                <span class="font-semibold">Payment Method:</span>
                <span class="uppercase">{{ strtoupper($sale->payment_method->value) }}</span>
            </div>
            @if ($sale->payment_reference)
                <div class="flex justify-between text-sm">
                    <span class="font-semibold">Payment Reference:</span>
                    <span>{{ $sale->payment_reference }}</span>
                </div>
            @endif
            <div class="flex justify-between text-sm">
                <span class="font-semibold">Payment Status:</span>
                <span class="uppercase">{{ $sale->payment_status ?? 'COMPLETED' }}</span>
            </div>
        </div>

        @if ($sale->notes)
            <div class="mb-6 border-t border-zinc-300 pt-4">
                <p class="text-sm font-semibold">Notes:</p>
                <p class="text-sm">{{ $sale->notes }}</p>
            </div>
        @endif

        {{-- Footer --}}
        <div class="mt-8 border-t-2 border-zinc-300 pt-4 text-center">
            <p class="text-sm font-semibold">Thank you for your business!</p>
            @if ($settings->website)
                <p class="text-sm text-zinc-600">{{ $settings->website }}</p>
            @endif
            <p class="mt-4 text-xs text-zinc-500">
                This is a computer-generated receipt and does not require a signature.
            </p>
        </div>
    </div>
</div>
