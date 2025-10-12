<div>
    <div class="mb-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" class="mb-2">{{ __('New Sale') }}</flux:heading>
                <flux:text>{{ __('Quick checkout for direct product sales') }}</flux:text>
            </div>

            @if ($activeShift)
                <div
                    class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 dark:border-emerald-800 dark:bg-emerald-950">
                    <flux:icon.clock class="size-5 text-emerald-600 dark:text-emerald-400" />
                    <div>
                        <flux:text class="text-sm font-semibold text-emerald-900 dark:text-emerald-100">
                            {{ $activeShift->shift_name }}</flux:text>
                        <flux:text class="text-xs text-emerald-700 dark:text-emerald-300">Active Shift</flux:text>
                    </div>
                </div>
            @else
                <div
                    class="flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 dark:border-amber-800 dark:bg-amber-950">
                    <flux:icon.exclamation-triangle class="size-5 text-amber-600 dark:text-amber-400" />
                    <div>
                        <flux:text class="text-sm font-semibold text-amber-900 dark:text-amber-100">No Active Shift
                        </flux:text>
                        <flux:text class="text-xs text-amber-700 dark:text-amber-300">
                            <a href="{{ route('shifts.open') }}" wire:navigate
                                class="underline hover:text-amber-900 dark:hover:text-amber-100">Open a shift</a>
                        </flux:text>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Product Selection - Left Side --}}
        <div class="lg:col-span-2">
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">{{ __('Add Products') }}</flux:heading>

                {{-- Barcode Scanner Input --}}
                <div class="mb-4">
                    <flux:field>
                        <flux:label>{{ __('Scan Barcode') }}</flux:label>
                        <div class="flex gap-2">
                            <flux:input wire:model.live="barcodeInput" wire:keydown.enter="scanBarcode"
                                placeholder="{{ __('Scan or enter barcode...') }}" class="flex-1" autofocus />
                            <flux:button wire:click="scanBarcode" variant="ghost">
                                <flux:icon.magnifying-glass />
                            </flux:button>
                        </div>
                        @error('barcodeInput')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                </div>

                {{-- Search Products --}}
                <flux:input wire:model.live.debounce.300ms="searchTerm"
                    placeholder="{{ __('Search products by name, SKU, or barcode...') }}" class="mb-4" />

                {{-- Product Grid --}}
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse ($inventoryItems as $item)
                        <div wire:click="addToCart('{{ $item->id }}')"
                            class="group relative flex cursor-pointer flex-col rounded-lg border border-zinc-200 bg-white p-4 shadow-sm transition-all hover:border-emerald-500 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-emerald-500">
                            {{-- Product Name --}}
                            <div class="mb-2">
                                <flux:heading size="sm" class="line-clamp-2 text-zinc-900 dark:text-zinc-100">
                                    {{ $item->name }}
                                </flux:heading>
                            </div>

                            {{-- SKU and Stock Info --}}
                            <div class="mb-3 flex items-center gap-2 text-xs">
                                <flux:badge variant="outline" size="sm" class="font-mono">
                                    {{ $item->sku }}
                                </flux:badge>
                                <flux:text class="text-zinc-500 dark:text-zinc-400">
                                    <span
                                        class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $item->quantity }}</span>
                                    in stock
                                </flux:text>
                            </div>

                            {{-- Price and Add Button --}}
                            <div class="mt-auto flex items-center justify-between">
                                <div>
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Price</flux:text>
                                    <flux:text class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ format_currency($item->selling_price) }}
                                    </flux:text>
                                </div>
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 transition-colors group-hover:bg-emerald-600 group-hover:text-white dark:bg-emerald-900/30 dark:text-emerald-400 dark:group-hover:bg-emerald-600">
                                    <flux:icon.plus class="h-4 w-4" />
                                </div>
                            </div>

                            {{-- Low Stock Warning --}}
                            @if ($item->quantity <= $item->reorder_level)
                                <div class="absolute right-2 top-2">
                                    <flux:badge variant="warning" size="sm">
                                        Low Stock
                                    </flux:badge>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center">
                            <div
                                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <flux:icon.magnifying-glass class="h-8 w-8 text-zinc-400" />
                            </div>
                            <flux:heading size="sm" class="mb-2 text-zinc-700 dark:text-zinc-300">
                                {{ $searchTerm ? __('No products found') : __('No products available') }}
                            </flux:heading>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $searchTerm ? __('Try a different search term') : __('Add inventory items to get started') }}
                            </flux:text>
                        </div>
                    @endforelse
                </div>

                @error('cart')
                    <flux:text class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                @enderror
            </div>
        </div>

        {{-- Cart and Checkout - Right Side --}}
        <div class="lg:col-span-1">
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">{{ __('Cart') }}</flux:heading>

                {{-- Cart Items --}}
                @if (empty($cart))
                    <div class="mb-4 py-12 text-center">
                        <div
                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <flux:icon.shopping-cart class="h-8 w-8 text-zinc-400" />
                        </div>
                        <flux:heading size="sm" class="mb-2 text-zinc-700 dark:text-zinc-300">
                            {{ __('Cart is empty') }}
                        </flux:heading>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Add products to get started') }}
                        </flux:text>
                    </div>
                @else
                    <div class="mb-4 max-h-96 space-y-3 overflow-y-auto">
                        @foreach ($cart as $key => $item)
                            <div
                                class="group rounded-lg border border-zinc-200 bg-zinc-50/50 p-3 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50 dark:hover:border-zinc-600">
                                {{-- Item Header --}}
                                <div class="mb-2 flex items-start justify-between">
                                    <div class="min-w-0 flex-1">
                                        <flux:heading size="sm"
                                            class="mb-1 line-clamp-1 text-zinc-900 dark:text-zinc-100">
                                            {{ $item['name'] }}
                                        </flux:heading>
                                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                            SKU: {{ $item['sku'] }}
                                        </flux:text>
                                    </div>
                                    <button wire:click="removeFromCart('{{ $key }}')" type="button"
                                        class="ml-2 rounded-full p-1 text-zinc-400 transition-colors hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900/30 dark:hover:text-red-400">
                                        <flux:icon.trash class="h-4 w-4" />
                                    </button>
                                </div>

                                {{-- Quantity and Price Row --}}
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Qty:</flux:text>
                                        <div
                                            class="flex items-center rounded-lg border border-zinc-300 dark:border-zinc-600">
                                            <button type="button"
                                                wire:click="updateQuantity('{{ $key }}', {{ max(1, $item['quantity'] - 1) }})"
                                                class="px-2 py-1 text-zinc-600 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700"
                                                @if ($item['quantity'] <= 1) disabled @endif>
                                                <flux:icon.minus class="h-3 w-3" />
                                            </button>
                                            <input type="number" wire:model.live="cart.{{ $key }}.quantity"
                                                wire:change="updateQuantity('{{ $key }}', $event.target.value)"
                                                min="1" max="{{ $item['max_quantity'] }}"
                                                class="w-12 border-0 bg-transparent px-2 py-1 text-center text-sm font-medium focus:outline-none focus:ring-0 dark:text-zinc-100" />
                                            <button type="button"
                                                wire:click="updateQuantity('{{ $key }}', {{ min($item['max_quantity'], $item['quantity'] + 1) }})"
                                                class="px-2 py-1 text-zinc-600 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700"
                                                @if ($item['quantity'] >= $item['max_quantity']) disabled @endif>
                                                <flux:icon.plus class="h-3 w-3" />
                                            </button>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ format_currency($item['unit_price']) }} each
                                        </flux:text>
                                        <flux:text class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                            {{ format_currency($item['unit_price'] * $item['quantity']) }}
                                        </flux:text>
                                    </div>
                                </div>

                                {{-- Stock Warning --}}
                                @if ($item['quantity'] >= $item['max_quantity'])
                                    <div class="mt-2 rounded bg-amber-50 px-2 py-1 dark:bg-amber-900/20">
                                        <flux:text class="text-xs text-amber-700 dark:text-amber-400">
                                            Max stock reached ({{ $item['max_quantity'] }} available)
                                        </flux:text>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-4 flex justify-end border-t border-zinc-200 pt-3 dark:border-zinc-700">
                        <flux:button variant="ghost" size="sm" wire:click="clearCart">
                            <flux:icon.trash class="mr-1 h-4 w-4" />
                            {{ __('Clear Cart') }}
                        </flux:button>
                    </div>
                @endif

                {{-- Customer Selection --}}
                <flux:field class="mb-4">
                    <flux:label>{{ __('Customer (Optional)') }}</flux:label>
                    <flux:select wire:model="customerId">
                        <option value="">{{ __('Walk-in Customer') }}</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->full_name }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>

                {{-- Payment Method --}}
                <flux:field class="mb-4">
                    <flux:label>{{ __('Payment Method') }}</flux:label>
                    <flux:select wire:model="paymentMethod" required>
                        <option value="cash">{{ __('Cash') }}</option>
                        <option value="card">{{ __('Card') }}</option>
                        <option value="mobile_money">{{ __('Mobile Money') }}</option>
                        <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                    </flux:select>
                    @error('paymentMethod')
                        <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                    @enderror
                </flux:field>

                {{-- Discount --}}
                <flux:field class="mb-4">
                    <flux:label>{{ __('Discount Amount') }}</flux:label>
                    <flux:input wire:model.live="discountAmount" type="number" step="0.01" min="0"
                        :max="$this->subtotal()" />
                    @error('discountAmount')
                        <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                    @enderror
                </flux:field>

                {{-- Notes --}}
                <flux:field class="mb-4">
                    <flux:label>{{ __('Notes') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="2" maxlength="1000" />
                    @error('notes')
                        <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                    @enderror
                </flux:field>

                {{-- Totals --}}
                <div class="mb-4 space-y-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <div class="flex justify-between text-sm">
                        <flux:text>{{ __('Subtotal') }}</flux:text>
                        <flux:text class="font-medium">{{ format_currency($this->subtotal()) }}</flux:text>
                    </div>
                    @if ($discountAmount > 0)
                        <div class="flex justify-between text-sm">
                            <flux:text>{{ __('Discount') }}</flux:text>
                            <flux:text class="font-medium text-red-600 dark:text-red-400">
                                -{{ format_currency($discountAmount) }}
                            </flux:text>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <flux:text>{{ __('Tax') }} ({{ number_format($this->taxRate(), 2) }}%)</flux:text>
                        <flux:text class="font-medium">{{ format_currency($this->taxAmount()) }}</flux:text>
                    </div>
                    <div class="flex justify-between border-t border-zinc-200 pt-2 dark:border-zinc-700">
                        <flux:heading size="lg">{{ __('Total') }}</flux:heading>
                        <flux:heading size="lg" class="text-green-600 dark:text-green-400">
                            {{ format_currency($this->total()) }}
                        </flux:heading>
                    </div>
                </div>

                {{-- Checkout Buttons --}}
                <div class="flex gap-2">
                    <flux:button href="{{ route('pos.index') }}" wire:navigate variant="ghost" class="flex-1">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button wire:click="checkout" :disabled="empty($cart)" class="flex-1">
                        {{ __('Complete Sale') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </div>
</div>
