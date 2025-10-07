<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <flux:heading size="xl">{{ $item->name }}</flux:heading>
                @if ($item->status === 'active')
                    <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                @else
                    <flux:badge color="zinc" size="sm">{{ __('Inactive') }}</flux:badge>
                @endif
                @if ($item->isLowStock())
                    <flux:badge color="red" size="sm">{{ __('Low Stock') }}</flux:badge>
                @endif
            </div>
            <flux:text class="mt-1">SKU: {{ $item->sku }} • Created {{ $item->created_at->format('M d, Y') }}
            </flux:text>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('inventory.index') }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Back to Inventory') }}
            </a>

            @can('update', $item)
                <a href="{{ route('inventory.edit', $item) }}" wire:navigate
                    class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    {{ __('Edit Item') }}
                </a>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main Content --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Item Details Card --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">{{ __('Item Details') }}</flux:heading>

                <div class="space-y-4">
                    {{-- Description --}}
                    @if ($item->description)
                        <div>
                            <flux:subheading>{{ __('Description') }}</flux:subheading>
                            <flux:text class="mt-1 whitespace-pre-wrap">{{ $item->description }}</flux:text>
                        </div>
                    @endif

                    {{-- Category --}}
                    @if ($item->category)
                        <div>
                            <flux:subheading>{{ __('Category') }}</flux:subheading>
                            <flux:text class="mt-1">{{ $item->category }}</flux:text>
                        </div>
                    @endif

                    <hr class="border-zinc-200 dark:border-zinc-700" />

                    {{-- Pricing Information --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <flux:subheading>{{ __('Cost Price') }}</flux:subheading>
                            <flux:text class="mt-1 text-lg font-semibold">${{ number_format($item->cost_price, 2) }}
                            </flux:text>
                            <flux:text class="text-xs">{{ __('What you pay') }}</flux:text>
                        </div>
                        <div>
                            <flux:subheading>{{ __('Selling Price') }}</flux:subheading>
                            <flux:text class="mt-1 text-lg font-semibold">${{ number_format($item->selling_price, 2) }}
                            </flux:text>
                            <flux:text class="text-xs">{{ __('What you charge') }}</flux:text>
                        </div>
                    </div>

                    {{-- Profit Margin --}}
                    @php
                        $profit = $item->selling_price - $item->cost_price;
                        $margin = $item->cost_price > 0 ? ($profit / $item->cost_price) * 100 : 0;
                    @endphp
                    <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900">
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:subheading>{{ __('Profit per Unit') }}</flux:subheading>
                                <flux:text class="mt-1 text-lg font-semibold text-green-600 dark:text-green-400">
                                    ${{ number_format($profit, 2) }}
                                </flux:text>
                            </div>
                            <div class="text-right">
                                <flux:subheading>{{ __('Margin') }}</flux:subheading>
                                <flux:text class="mt-1 text-lg font-semibold">{{ number_format($margin, 1) }}%
                                </flux:text>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Inventory Status Card --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">{{ __('Inventory Status') }}</flux:heading>

                <div class="space-y-4">
                    <div>
                        <flux:subheading>{{ __('Current Quantity') }}</flux:subheading>
                        <div class="mt-1 flex items-baseline gap-2">
                            <span
                                class="text-3xl font-bold {{ $item->isLowStock() ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-white' }}">
                                {{ $item->quantity }}
                            </span>
                            <flux:text>{{ __('units') }}</flux:text>
                        </div>
                    </div>

                    <div>
                        <flux:subheading>{{ __('Reorder Level') }}</flux:subheading>
                        <flux:text class="mt-1">{{ $item->reorder_level }} {{ __('units') }}</flux:text>
                    </div>

                    @if ($item->isLowStock())
                        <div class="rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
                            <div class="flex items-start gap-2">
                                <svg class="mt-0.5 h-5 w-5 text-red-600 dark:text-red-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div>
                                    <flux:text class="text-sm font-medium text-red-800 dark:text-red-200">
                                        {{ __('Low Stock Alert') }}
                                    </flux:text>
                                    <flux:text class="text-xs text-red-700 dark:text-red-300">
                                        {{ __('Stock is at or below reorder level') }}
                                    </flux:text>
                                </div>
                            </div>
                        </div>
                    @endif

                    <hr class="border-zinc-200 dark:border-zinc-700" />

                    {{-- Inventory Value --}}
                    <div>
                        <flux:subheading>{{ __('Total Value (Cost)') }}</flux:subheading>
                        <flux:text class="mt-1 text-xl font-semibold">
                            ${{ number_format($item->quantity * $item->cost_price, 2) }}
                        </flux:text>
                    </div>

                    <div>
                        <flux:subheading>{{ __('Total Value (Retail)') }}</flux:subheading>
                        <flux:text class="mt-1 text-xl font-semibold">
                            ${{ number_format($item->quantity * $item->selling_price, 2) }}
                        </flux:text>
                    </div>

                    @can('adjustQuantity', $item)
                        <hr class="border-zinc-200 dark:border-zinc-700" />

                        <flux:button type="button" wire:click="openAdjustModal" variant="primary" class="w-full">
                            <div class="flex items-center justify-center gap-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                <span>{{ __('Adjust Quantity') }}</span>
                            </div>
                        </flux:button>
                    @endcan
                </div>
            </div>

            {{-- Quick Info Card --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">{{ __('Quick Info') }}</flux:heading>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <flux:text class="text-sm">{{ __('Status') }}</flux:text>
                        @if ($item->status === 'active')
                            <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">{{ __('Inactive') }}</flux:badge>
                        @endif
                    </div>

                    <div class="flex items-center justify-between">
                        <flux:text class="text-sm">{{ __('Created') }}</flux:text>
                        <flux:text class="text-sm">{{ $item->created_at->format('M d, Y') }}</flux:text>
                    </div>

                    <div class="flex items-center justify-between">
                        <flux:text class="text-sm">{{ __('Last Updated') }}</flux:text>
                        <flux:text class="text-sm">{{ $item->updated_at->format('M d, Y') }}</flux:text>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Adjustment History --}}
    @if ($adjustments->isNotEmpty())
        <div class="mt-6">
            <flux:heading size="lg" class="mb-4">{{ __('Adjustment History') }}</flux:heading>

            <div
                class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-900">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Date') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Type') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Quantity Change') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Before → After') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Reason') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Adjusted By') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                            @foreach ($adjustments as $adjustment)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $adjustment->created_at->format('M d, Y') }}
                                        <span class="text-zinc-500 dark:text-zinc-400">
                                            {{ $adjustment->created_at->format('g:i A') }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        @if ($adjustment->quantity_change > 0)
                                            <flux:badge color="green" size="sm">{{ __('Added') }}
                                            </flux:badge>
                                        @else
                                            <flux:badge color="red" size="sm">{{ __('Removed') }}
                                            </flux:badge>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span
                                            class="font-semibold {{ $adjustment->quantity_change > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $adjustment->quantity_change > 0 ? '+' : '' }}{{ $adjustment->quantity_change }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $adjustment->quantity_before }} → {{ $adjustment->quantity_after }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-zinc-900 dark:text-zinc-100">
                                        <div>{{ $adjustment->reason }}</div>
                                        @if ($adjustment->notes)
                                            <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $adjustment->notes }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $adjustment->adjustedBy->name }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Adjustment Modal --}}
    <flux:modal name="adjust-inventory" :show="$showAdjustModal" class="max-w-md">
        <form wire:submit="saveAdjustment">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Adjust Inventory Quantity') }}</flux:heading>
                    <flux:subheading class="mt-2">
                        {{ __('Add or remove inventory for') }} {{ $item->name }}
                    </flux:subheading>
                </div>

                <div class="space-y-4">
                    {{-- Adjustment Type --}}
                    <flux:field>
                        <flux:label>{{ __('Type') }}</flux:label>
                        <flux:select wire:model="adjustmentType">
                            <option value="add">{{ __('Add Inventory') }}</option>
                            <option value="remove">{{ __('Remove Inventory') }}</option>
                        </flux:select>
                        <flux:error name="adjustmentType" />
                    </flux:field>

                    {{-- Quantity --}}
                    <flux:field>
                        <flux:label>{{ __('Quantity') }}</flux:label>
                        <flux:input wire:model="adjustmentQuantity" type="number" min="1"
                            placeholder="Enter quantity" />
                        <flux:error name="adjustmentQuantity" />
                    </flux:field>

                    {{-- Reason --}}
                    <flux:field>
                        <flux:label>{{ __('Reason') }}</flux:label>
                        <flux:input wire:model="adjustmentReason" placeholder="e.g., Restock, Damaged, Theft" />
                        <flux:error name="adjustmentReason" />
                    </flux:field>

                    {{-- Notes (Optional) --}}
                    <flux:field>
                        <flux:label>{{ __('Notes (Optional)') }}</flux:label>
                        <flux:textarea wire:model="adjustmentNotes" rows="3"
                            placeholder="Additional details about this adjustment" />
                        <flux:error name="adjustmentNotes" />
                    </flux:field>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="closeAdjustModal">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ __('Save Adjustment') }}
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>
