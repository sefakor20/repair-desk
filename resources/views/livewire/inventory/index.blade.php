<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" class="mb-2">{{ __('Inventory') }}</flux:heading>
                <flux:text>{{ __('Manage parts and products inventory') }}</flux:text>
            </div>
            <flux:button :href="route('inventory.create')" wire:navigate>
                <flux:icon.plus class="-ml-1 mr-2 size-5" />
                {{ __('Add Item') }}
            </flux:button>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="mb-6 space-y-4">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <flux:input wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('Search by name, SKU, or description...') }}" />
            </div>

            <select wire:model.live="status"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="active">{{ __('Active') }}</option>
                <option value="inactive">{{ __('Inactive') }}</option>
            </select>

            <select wire:model.live="category"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">{{ __('All Categories') }}</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2">
                    <flux:checkbox wire:model.live="lowStock" />
                    <flux:text class="text-sm">{{ __('Show only low stock items') }}</flux:text>
                </label>
            </div>

            @if ($search || $status || $category || $lowStock)
                <flux:button variant="ghost" size="sm" wire:click="clearFilters">
                    {{ __('Clear filters') }}
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Inventory Table --}}
    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        @if ($items->isEmpty())
            <div class="p-6 text-center">
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    @if ($search || $status || $category || $lowStock)
                        {{ __('No inventory items found matching your filters.') }}
                    @else
                        {{ __('No inventory items yet.') }}
                    @endif
                </flux:text>
            </div>
        @else
            <table class="w-full">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Item') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('SKU') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Category') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Quantity') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Cost Price') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Selling Price') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Status') }}
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($items as $item)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div>
                                        {{-- <a href="{{ route('inventory.show', $item) }}" class="font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" wire:navigate>
                                            {{ $item->name }}
                                        </a> --}}
                                        <span class="font-medium">{{ $item->name }}</span>
                                        @if ($item->description)
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ Str::limit($item->description, 40) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $item->sku }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $item->category ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="font-medium {{ $item->isLowStock() ? 'text-red-600 dark:text-red-400' : '' }}">
                                        {{ $item->quantity }}
                                    </span>
                                    @if ($item->isLowStock())
                                        <flux:badge color="red" size="sm">
                                            {{ __('Low') }}
                                        </flux:badge>
                                    @endif
                                </div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ __('Reorder at: :level', ['level' => $item->reorder_level]) }}
                                </p>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                ${{ number_format($item->cost_price, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                ${{ number_format($item->selling_price, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @if ($item->status === 'active')
                                    <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">{{ __('Inactive') }}</flux:badge>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- <flux:button size="sm" variant="ghost" :href="route('inventory.edit', $item)" wire:navigate>
                                        {{ __('Edit') }}
                                    </flux:button> --}}
                                    <flux:button size="sm" variant="danger"
                                        wire:click="delete({{ $item->id }})"
                                        wire:confirm="{{ __('Are you sure you want to delete this item?') }}">
                                        {{ __('Delete') }}
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
