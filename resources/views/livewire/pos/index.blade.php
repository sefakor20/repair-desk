<div>
    {{-- Success Message --}}
    @if ($showSuccessMessage)
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => {
            show = false;
            $wire.set('showSuccessMessage', false)
        }, 5000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="mb-6 rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
            <div class="flex items-center">
                <flux:icon.check-circle class="mr-3 size-5 text-green-400" />
                <p class="text-sm font-medium text-green-800 dark:text-green-200">Sale completed successfully!</p>
            </div>
        </div>
    @endif

    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" class="mb-2">{{ __('Point of Sale') }}</flux:heading>
                <flux:text>{{ __('Quick sales and checkout') }}</flux:text>
            </div>
            @can('create', App\Models\PosSale::class)
                <flux:button :href="route('pos.create')" wire:navigate>
                    <flux:icon.plus class="-ml-1 mr-2 size-5" />
                    {{ __('New Sale') }}
                </flux:button>
            @endcan
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="mb-6 space-y-4">
        <div class="grid gap-4 sm:grid-cols-3">
            <flux:input wire:model.live.debounce.300ms="searchTerm"
                placeholder="{{ __('Search by sale number or customer...') }}" />

            <select wire:model.live="statusFilter"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="completed">{{ __('Completed') }}</option>
                <option value="refunded">{{ __('Refunded') }}</option>
            </select>

            <select wire:model.live="paymentMethodFilter"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">{{ __('All Payment Methods') }}</option>
                <option value="cash">{{ __('Cash') }}</option>
                <option value="card">{{ __('Card') }}</option>
                <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
            </select>
        </div>

        @if ($searchTerm || $statusFilter || $paymentMethodFilter)
            <div class="flex justify-end">
                <flux:button variant="ghost" size="sm" wire:click="clearFilters">
                    {{ __('Clear filters') }}
                </flux:button>
            </div>
        @endif
    </div>

    {{-- Desktop Sales Table (hidden on mobile) --}}
    <div
        class="hidden overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800 lg:block">
        @if ($sales->isEmpty())
            <div class="p-6 text-center">
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    @if ($searchTerm || $statusFilter || $paymentMethodFilter)
                        {{ __('No sales found matching your filters.') }}
                    @else
                        {{ __('No sales yet. Create your first sale to get started.') }}
                    @endif
                </flux:text>
            </div>
        @else
            <table class="w-full">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Sale #') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Customer') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Items') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Total') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Payment') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Status') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Date') }}
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($sales as $sale)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                            <td class="px-6 py-4">
                                <a href="{{ route('pos.show', $sale) }}"
                                    class="font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                    wire:navigate>
                                    {{ $sale->sale_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $sale->customer ? $sale->customer->full_name : __('Walk-in Customer') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $sale->total_items }} {{ __('items') }}
                            </td>
                            <td
                                class="whitespace-nowrap px-6 py-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ format_currency($sale->total_amount) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <flux:badge size="sm">{{ $sale->payment_method->label() }}</flux:badge>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <flux:badge :color="$sale->status->color()" size="sm">
                                    {{ $sale->status->label() }}
                                </flux:badge>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $sale->sale_date->format('M d, Y') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('pos.show', $sale) }}" wire:navigate
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                        {{ __('View') }}
                                    </a>
                                    @if ($sale->status === App\Enums\PosSaleStatus::Completed && !$sale->hasReturns())
                                        <a href="{{ route('pos.returns.create', $sale) }}" wire:navigate
                                            class="text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300">
                                            {{ __('Return') }}
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Mobile Card View (visible on mobile) --}}
    <div class="space-y-4 lg:hidden">
        @if ($sales->isEmpty())
            <div
                class="rounded-lg border border-zinc-200 bg-white p-6 text-center dark:border-zinc-700 dark:bg-zinc-800">
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    @if ($searchTerm || $statusFilter || $paymentMethodFilter)
                        {{ __('No sales found matching your filters.') }}
                    @else
                        {{ __('No sales yet. Create your first sale to get started.') }}
                    @endif
                </flux:text>
            </div>
        @else
            @foreach ($sales as $sale)
                <div
                    class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm transition-shadow hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800">
                    <!-- Sale Header -->
                    <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <a href="{{ route('pos.show', $sale) }}"
                                    class="text-sm font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                    wire:navigate>
                                    {{ $sale->sale_number }}
                                </a>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <flux:badge :color="$sale->status->color()" size="sm">
                                        {{ $sale->status->label() }}
                                    </flux:badge>
                                    <flux:badge size="sm">{{ $sale->payment_method->label() }}</flux:badge>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-semibold text-zinc-900 dark:text-white">
                                    {{ format_currency($sale->total_amount) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sale Details -->
                    <div class="px-4 py-3">
                        <dl class="space-y-2.5">
                            <div class="flex items-center justify-between text-sm">
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Customer</dt>
                                <dd class="text-zinc-900 dark:text-white">
                                    {{ $sale->customer ? $sale->customer->full_name : __('Walk-in Customer') }}
                                </dd>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Items</dt>
                                <dd class="text-zinc-900 dark:text-white">{{ $sale->total_items }} {{ __('items') }}
                                </dd>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Date</dt>
                                <dd class="text-zinc-900 dark:text-white">{{ $sale->sale_date->format('M d, Y') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Actions -->
                    <div class="border-t border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-center justify-end gap-2">
                            @if ($sale->status === App\Enums\PosSaleStatus::Completed && !$sale->hasReturns())
                                <a href="{{ route('pos.returns.create', $sale) }}" wire:navigate
                                    class="inline-flex items-center gap-2 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-700 transition-colors hover:bg-amber-100 dark:border-amber-600 dark:bg-amber-900/20 dark:text-amber-300 dark:hover:bg-amber-900/40">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    {{ __('Return') }}
                                </a>
                            @endif
                            <a href="{{ route('pos.show', $sale) }}" wire:navigate
                                class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                {{ __('View') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- Pagination --}}
    @if ($sales->hasPages())
        <div class="mt-6">
            {{ $sales->links() }}
        </div>
    @endif
</div>
