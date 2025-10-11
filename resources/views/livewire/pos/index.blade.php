<div>
    {{-- Success Message --}}
    @if ($showSuccessMessage)
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => { show = false;
            $wire.set('showSuccessMessage', false) }, 5000)"
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

    {{-- Sales Table --}}
    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
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
                                ${{ number_format($sale->total_amount, 2) }}
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
                                <a href="{{ route('pos.show', $sale) }}" wire:navigate
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ __('View') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Pagination --}}
    @if ($sales->hasPages())
        <div class="mt-6">
            {{ $sales->links() }}
        </div>
    @endif
</div>
