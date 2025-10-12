<div class="space-y-6">
    {{-- Alert Summary Cards --}}
    <div class="grid gap-4 md:grid-cols-3">
        {{-- Low Stock Card --}}
        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-6 dark:border-yellow-900 dark:bg-yellow-900/20">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Low Stock</p>
                    <p class="mt-2 text-3xl font-bold text-yellow-900 dark:text-yellow-100">
                        {{ $this->lowStockItems->count() }}
                    </p>
                </div>
                <div class="rounded-full bg-yellow-100 p-3 dark:bg-yellow-800">
                    <svg class="h-8 w-8 text-yellow-600 dark:text-yellow-200" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Critical Stock Card --}}
        <div class="rounded-lg border border-orange-200 bg-orange-50 p-6 dark:border-orange-900 dark:bg-orange-900/20">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-orange-800 dark:text-orange-200">Critical</p>
                    <p class="mt-2 text-3xl font-bold text-orange-900 dark:text-orange-100">
                        {{ $this->criticalItems->count() }}
                    </p>
                </div>
                <div class="rounded-full bg-orange-100 p-3 dark:bg-orange-800">
                    <svg class="h-8 w-8 text-orange-600 dark:text-orange-200" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Out of Stock Card --}}
        <div class="rounded-lg border border-red-200 bg-red-50 p-6 dark:border-red-900 dark:bg-red-900/20">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-red-800 dark:text-red-200">Out of Stock</p>
                    <p class="mt-2 text-3xl font-bold text-red-900 dark:text-red-100">
                        {{ $this->outOfStockItems->count() }}
                    </p>
                </div>
                <div class="rounded-full bg-red-100 p-3 dark:bg-red-800">
                    <svg class="h-8 w-8 text-red-600 dark:text-red-200" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="flex gap-2 border-b border-zinc-200 dark:border-zinc-700">
        <button wire:click="$set('alertType', 'all')"
            class="border-b-2 px-4 py-2 text-sm font-medium transition-colors {{ $alertType === 'all' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
            All Alerts ({{ $this->totalAlerts }})
        </button>
        <button wire:click="$set('alertType', 'critical')"
            class="border-b-2 px-4 py-2 text-sm font-medium transition-colors {{ $alertType === 'critical' ? 'border-orange-500 text-orange-600 dark:text-orange-400' : 'border-transparent text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
            Critical ({{ $this->criticalItems->count() }})
        </button>
        <button wire:click="$set('alertType', 'low')"
            class="border-b-2 px-4 py-2 text-sm font-medium transition-colors {{ $alertType === 'low' ? 'border-yellow-500 text-yellow-600 dark:text-yellow-400' : 'border-transparent text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
            Low Stock ({{ $this->lowStockItems->count() }})
        </button>
        <button wire:click="$set('alertType', 'out')"
            class="border-b-2 px-4 py-2 text-sm font-medium transition-colors {{ $alertType === 'out' ? 'border-red-500 text-red-600 dark:text-red-400' : 'border-transparent text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
            Out of Stock ({{ $this->outOfStockItems->count() }})
        </button>
    </div>

    {{-- Items List --}}
    @if ($this->displayItems->isEmpty())
        <div
            class="rounded-lg border-2 border-dashed border-zinc-200 bg-zinc-50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-zinc-900 dark:text-zinc-100">All Good!</h3>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                No items matching this alert type. Your inventory levels look healthy.
            </p>
        </div>
    @else
        <div
            class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-900">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Item
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            SKU
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Category
                        </th>
                        <th
                            class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Current Stock
                        </th>
                        <th
                            class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Reorder Level
                        </th>
                        <th
                            class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Status
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                    @foreach ($this->displayItems as $item)
                        <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-900">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $item->name }}
                                        </div>
                                        @if ($item->description)
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ Str::limit($item->description, 50) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <code
                                    class="rounded bg-zinc-100 px-2 py-1 text-xs font-mono text-zinc-800 dark:bg-zinc-900 dark:text-zinc-200">
                                    {{ $item->sku }}
                                </code>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $item->category ?? 'Uncategorized' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                <span
                                    class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $item->quantity === 0 ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : ($item->quantity <= $item->reorder_level / 2 ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200') }}">
                                    {{ $item->quantity }}
                                </span>
                            </td>
                            <td
                                class="whitespace-nowrap px-6 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $item->reorder_level }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex justify-center">
                                    @if ($item->quantity === 0)
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200">
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Out
                                        </span>
                                    @elseif ($item->isCriticallyLowStock())
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Critical
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Low
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <flux:button variant="ghost" size="sm"
                                    href="{{ route('inventory.show', $item) }}" wire:navigate>
                                    View
                                </flux:button>
                                <flux:button variant="primary" size="sm"
                                    href="{{ route('inventory.edit', $item) }}" wire:navigate>
                                    Restock
                                </flux:button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
