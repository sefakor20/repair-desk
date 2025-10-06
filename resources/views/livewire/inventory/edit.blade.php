<div>
    {{-- Breadcrumb --}}
    <div class="mb-6 flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
        <a href="{{ route('inventory.index') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-white">
            {{ __('Inventory') }}
        </a>
        <span>/</span>
        <span class="text-zinc-900 dark:text-white">{{ __('Edit') }}</span>
    </div>

    {{-- Header --}}
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">{{ __('Edit Inventory Item') }}</flux:heading>
        <flux:text>{{ __('Update item information and stock levels') }}</flux:text>
    </div>

    {{-- Form --}}
    <form wire:submit="update">
        <div class="space-y-6">
            {{-- Basic Information --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">{{ __('Basic Information') }}</flux:heading>

                <div class="grid gap-6 md:grid-cols-2">
                    {{-- Name --}}
                    <div>
                        <flux:field>
                            <flux:label for="name">{{ __('Item Name') }} <span class="text-red-500">*</span>
                            </flux:label>
                            <flux:input wire:model="name" id="name" placeholder="iPhone 13 Screen"
                                :invalid="$errors->has('name')" />
                            <flux:error name="name" />
                        </flux:field>
                    </div>

                    {{-- SKU --}}
                    <div>
                        <flux:field>
                            <flux:label for="sku">{{ __('SKU') }} <span class="text-red-500">*</span>
                            </flux:label>
                            <flux:input wire:model="sku" id="sku" placeholder="IP13-SCR-001"
                                :invalid="$errors->has('sku')" />
                            <flux:error name="sku" />
                        </flux:field>
                    </div>

                    {{-- Category --}}
                    <div>
                        <flux:field>
                            <flux:label for="category">{{ __('Category') }}</flux:label>
                            <flux:input wire:model="category" id="category" placeholder="Parts, Tools, Accessories"
                                list="category-list" :invalid="$errors->has('category')" />
                            <datalist id="category-list">
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                            <flux:error name="category" />
                        </flux:field>
                    </div>

                    {{-- Status --}}
                    <div>
                        <flux:field>
                            <flux:label for="status">{{ __('Status') }} <span class="text-red-500">*</span>
                            </flux:label>
                            <select wire:model="status" id="status"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
                            <flux:error name="status" />
                        </flux:field>
                    </div>
                </div>

                {{-- Description --}}
                <div class="mt-6">
                    <flux:field>
                        <flux:label for="description">{{ __('Description') }}</flux:label>
                        <flux:textarea wire:model="description" id="description" rows="3"
                            placeholder="Original Apple replacement screen for iPhone 13..."
                            :invalid="$errors->has('description')" />
                        <flux:error name="description" />
                    </flux:field>
                </div>
            </div>

            {{-- Pricing --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">{{ __('Pricing') }}</flux:heading>

                <div class="grid gap-6 md:grid-cols-2">
                    {{-- Cost Price --}}
                    <div>
                        <flux:field>
                            <flux:label for="cost_price">{{ __('Cost Price') }} <span class="text-red-500">*</span>
                            </flux:label>
                            <flux:input wire:model="cost_price" id="cost_price" type="number" step="0.01"
                                min="0" placeholder="0.00" :invalid="$errors->has('cost_price')" />
                            <flux:description>{{ __('What you pay for this item') }}</flux:description>
                            <flux:error name="cost_price" />
                        </flux:field>
                    </div>

                    {{-- Selling Price --}}
                    <div>
                        <flux:field>
                            <flux:label for="selling_price">{{ __('Selling Price') }} <span
                                    class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="selling_price" id="selling_price" type="number" step="0.01"
                                min="0" placeholder="0.00" :invalid="$errors->has('selling_price')" />
                            <flux:description>{{ __('What you charge customers') }}</flux:description>
                            <flux:error name="selling_price" />
                        </flux:field>
                    </div>
                </div>
            </div>

            {{-- Inventory --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">{{ __('Inventory') }}</flux:heading>

                <div class="grid gap-6 md:grid-cols-2">
                    {{-- Quantity --}}
                    <div>
                        <flux:field>
                            <flux:label for="quantity">{{ __('Current Quantity') }} <span class="text-red-500">*</span>
                            </flux:label>
                            <flux:input wire:model="quantity" id="quantity" type="number" min="0"
                                placeholder="0" :invalid="$errors->has('quantity')" />
                            <flux:description>{{ __('Current stock level') }}</flux:description>
                            <flux:error name="quantity" />
                        </flux:field>
                    </div>

                    {{-- Reorder Level --}}
                    <div>
                        <flux:field>
                            <flux:label for="reorder_level">{{ __('Reorder Level') }} <span
                                    class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="reorder_level" id="reorder_level" type="number" min="0"
                                placeholder="10" :invalid="$errors->has('reorder_level')" />
                            <flux:description>{{ __('Alert when stock reaches this level') }}</flux:description>
                            <flux:error name="reorder_level" />
                        </flux:field>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between">
                <a href="{{ route('inventory.index') }}" wire:navigate
                    class="rounded-md px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
                    {{ __('Cancel') }}
                </a>

                <flux:button type="submit" variant="primary">
                    {{ __('Update Item') }}
                </flux:button>
            </div>
        </div>
    </form>
</div>
