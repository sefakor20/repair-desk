<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" class="mb-2">{{ __('Inventory') }}</flux:heading>
                <flux:text>{{ __('Manage parts and products inventory') }}</flux:text>
            </div>
            @if (auth()->check() && auth()->user()->hasStaffPermission('manage_inventory'))
                <flux:button :href="route('inventory.create')" wire:navigate>
                    <flux:icon.plus class="-ml-1 mr-2 size-5" />
                    {{ __('Add Item') }}
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="mb-6 space-y-4">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <flux:input wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('Search by name, SKU, or description...') }}" />
            </div>

            <!-- Branch Filter -->
            <select wire:model.live="branchFilter"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">{{ __('All Branches') }}</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>

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

            @if ($search || $status || $category || $lowStock || $branchFilter)
                <flux:button variant="ghost" size="sm" wire:click="clearFilters">
                    {{ __('Clear filters') }}
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Desktop Inventory Table (hidden on mobile) --}}
    <div
        class="hidden overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800 lg:block">
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
                                        <a href="{{ route('inventory.show', $item) }}"
                                            class="font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                            wire:navigate>
                                            {{ $item->name }}
                                        </a>
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
                                {{ format_currency($item->cost_price) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ format_currency($item->selling_price) }}
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
                                    @if (auth()->check() && auth()->user()->hasStaffPermission('manage_inventory') && auth()->user()->can('update', $item))
                                        <a href="{{ route('inventory.edit', $item) }}" wire:navigate
                                            class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white"
                                            title="Edit">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endif

                                    @if (auth()->check() && auth()->user()->hasStaffPermission('manage_inventory') && auth()->user()->can('delete', $item))
                                        <button wire:click="confirmDelete('{{ $item->id }}')"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            title="Delete">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endif
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

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-confirmation" :show="$deletingItemId !== null" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete Inventory Item') }}</flux:heading>
                <flux:subheading class="mt-2">
                    {{ __('Are you sure you want to delete this inventory item? This action cannot be undone.') }}
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="cancelDelete">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="danger" wire:click="delete">
                    {{ __('Delete Item') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
