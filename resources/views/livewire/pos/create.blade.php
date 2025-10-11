<div>
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">{{ __('New Sale') }}</flux:heading>
        <flux:text>{{ __('Quick checkout for direct product sales') }}</flux:text>
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
                <div class="grid gap-3 sm:grid-cols-2">
                    @forelse ($inventoryItems as $item)
                        <div wire:click="addToCart('{{ $item->id }}')"
                            class="flex cursor-pointer items-center justify-between rounded-lg border border-zinc-200 p-3 transition hover:border-blue-500 hover:bg-blue-50 dark:border-zinc-700 dark:hover:border-blue-500 dark:hover:bg-blue-900/20">
                            <div class="min-w-0 flex-1">
                                <flux:text class="font-medium">{{ $item->name }}</flux:text>
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $item->sku }} • {{ $item->quantity }} {{ __('in stock') }}
                                </flux:text>
                            </div>
                            <div class="ml-3 text-right">
                                <flux:text class="font-semibold text-green-600 dark:text-green-400">
                                    ${{ number_format($item->selling_price, 2) }}
                                </flux:text>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 py-8 text-center">
                            <flux:text class="text-zinc-500 dark:text-zinc-400">
                                {{ $searchTerm ? __('No products found.') : __('No products available.') }}
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
                    <div class="mb-4 py-8 text-center">
                        <flux:text class="text-zinc-500 dark:text-zinc-400">
                            {{ __('Cart is empty. Add products to get started.') }}
                        </flux:text>
                    </div>
                @else
                    <div class="mb-4 space-y-2">
                        @foreach ($cart as $key => $item)
                            <div
                                class="flex items-center gap-2 rounded-lg border border-zinc-200 p-2 dark:border-zinc-700">
                                <div class="min-w-0 flex-1">
                                    <flux:text class="text-sm font-medium">{{ $item['name'] }}</flux:text>
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                        ${{ number_format($item['unit_price'], 2) }}
                                        {{ __('each') }}
                                    </flux:text>
                                </div>
                                <input type="number" wire:model.live="cart.{{ $key }}.quantity"
                                    wire:change="updateQuantity('{{ $key }}', $event.target.value)"
                                    min="1" max="{{ $item['max_quantity'] }}"
                                    class="w-16 rounded-md border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900" />
                                <button wire:click="removeFromCart('{{ $key }}')"
                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                    <flux:icon.x-mark class="size-5" />
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-4 flex justify-end">
                        <flux:button variant="ghost" size="sm" wire:click="clearCart">
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
                        <flux:text class="font-medium">${{ number_format($this->subtotal(), 2) }}</flux:text>
                    </div>
                    @if ($discountAmount > 0)
                        <div class="flex justify-between text-sm">
                            <flux:text>{{ __('Discount') }}</flux:text>
                            <flux:text class="font-medium text-red-600 dark:text-red-400">
                                -${{ number_format($discountAmount, 2) }}
                            </flux:text>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <flux:text>{{ __('Tax') }} ({{ number_format($this->taxRate(), 2) }}%)</flux:text>
                        <flux:text class="font-medium">${{ number_format($this->taxAmount(), 2) }}</flux:text>
                    </div>
                    <div class="flex justify-between border-t border-zinc-200 pt-2 dark:border-zinc-700">
                        <flux:heading size="lg">{{ __('Total') }}</flux:heading>
                        <flux:heading size="lg" class="text-green-600 dark:text-green-400">
                            ${{ number_format($this->total(), 2) }}
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
