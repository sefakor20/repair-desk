<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Process Return</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Original Sale: <span class="font-mono font-semibold">{{ $sale->sale_number }}</span>
            </p>
        </div>
        <flux:button variant="ghost" href="{{ route('pos.show', $sale) }}" wire:navigate>
            <flux:icon.arrow-left class="mr-2" />
            Back to Sale
        </flux:button>
    </div>

    @if (!$sale->canBeReturned())
        <flux:callout variant="danger">
            <strong>Return Window Expired</strong>
            <p class="mt-1">This sale is outside the return window and cannot be returned.</p>
        </flux:callout>
    @endif

    <form wire:submit="processReturn" class="space-y-6">
        {{-- Sale Information --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Sale Information</h2>
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Customer</p>
                    <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                        {{ $sale->customer ? $sale->customer->full_name : 'Walk-in Customer' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Sale Date</p>
                    <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                        {{ $sale->sale_date->format('M d, Y h:i A') }}
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Original Total</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ format_currency($sale->total_amount) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Return Reason --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Return Details</h2>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:field label="Return Reason" required>
                    <flux:select wire:model.live="returnReason">
                        <option value="">Select a reason...</option>
                        @foreach (App\Enums\ReturnReason::cases() as $reason)
                            <option value="{{ $reason->value }}">{{ $reason->label() }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field label="Restocking Fee %">
                    <flux:input type="number" step="0.01" min="0" max="100"
                        wire:model.live="restockingFeePercentage" />
                </flux:field>
            </div>

            <flux:field label="Return Notes" class="mt-4">
                <flux:textarea rows="3" wire:model="returnNotes"
                    placeholder="Optional notes about this return..." />
            </flux:field>

            <div class="mt-4 space-y-2">
                <flux:checkbox wire:model="autoApprove">
                    Auto-approve return (process immediately)
                </flux:checkbox>
                <flux:checkbox wire:model="restoreInventory">
                    Restore items to inventory
                </flux:checkbox>
            </div>
        </div>

        {{-- Items to Return --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Select Items to Return</h2>

            @error('selectedItems')
                <flux:callout variant="danger" class="mb-4">{{ $message }}</flux:callout>
            @enderror

            <div class="space-y-4">
                @forelse($this->returnableItems as $item)
                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700"
                        wire:key="item-{{ $item->id }}">
                        <div class="flex items-start gap-4">
                            <div class="pt-1">
                                <flux:checkbox wire:model.live="selectedItems.{{ $item->id }}.selected" />
                            </div>

                            <div class="flex-1">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $item->inventoryItem->name }}
                                        </h3>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                            SKU: {{ $item->inventoryItem->sku }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ format_currency($item->unit_price) }}
                                        </p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                            Qty sold: {{ $item->quantity }}
                                        </p>
                                    </div>
                                </div>

                                @if ($selectedItems[$item->id]['selected'] ?? false)
                                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                                        <flux:field label="Quantity to Return">
                                            <flux:input type="number" min="1" max="{{ $item->quantity }}"
                                                wire:model.live="selectedItems.{{ $item->id }}.quantity" />
                                        </flux:field>

                                        <flux:field label="Item Condition">
                                            <flux:select wire:model="selectedItems.{{ $item->id }}.condition">
                                                <option value="good">Good</option>
                                                <option value="damaged">Damaged</option>
                                                <option value="defective">Defective</option>
                                            </flux:select>
                                        </flux:field>

                                        <div class="md:col-span-2">
                                            <flux:field label="Item Notes">
                                                <flux:input wire:model="selectedItems.{{ $item->id }}.notes"
                                                    placeholder="Optional notes about this item..." />
                                            </flux:field>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border-2 border-dashed border-zinc-200 p-8 text-center dark:border-zinc-700">
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            All items from this sale have already been returned.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Return Summary --}}
        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Return Summary</h2>

            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-zinc-600 dark:text-zinc-400">Subtotal Returned:</span>
                    <span class="font-medium text-zinc-900 dark:text-zinc-100">
                        {{ format_currency($this->subtotalReturned) }}
                    </span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-zinc-600 dark:text-zinc-400">Tax Returned:</span>
                    <span class="font-medium text-zinc-900 dark:text-zinc-100">
                        {{ format_currency($this->taxReturned) }}
                    </span>
                </div>
                @if ($this->restockingFee > 0)
                    <div class="flex justify-between text-sm text-orange-600 dark:text-orange-400">
                        <span>Restocking Fee ({{ $restockingFeePercentage }}%):</span>
                        <span class="font-medium">-{{ format_currency($this->restockingFee) }}</span>
                    </div>
                @endif
                <div class="flex justify-between border-t border-zinc-200 pt-2 text-lg font-bold dark:border-zinc-700">
                    <span class="text-zinc-900 dark:text-zinc-100">Total Refund:</span>
                    <span class="text-green-600 dark:text-green-400">
                        {{ format_currency($this->totalRefund) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3">
            <flux:button variant="ghost" href="{{ route('pos.show', $sale) }}" wire:navigate>
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary">
                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span wire:loading.remove wire:target="processReturn">Process Return</span>
                <span wire:loading wire:target="processReturn">Processing...</span>
            </flux:button>
        </div>
    </form>
</div>
