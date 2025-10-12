<div>
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" class="mb-2">{{ __('Sales Analytics') }}</flux:heading>
                <flux:subheading>{{ __('Monitor your sales performance and key metrics') }}</flux:subheading>
            </div>

            {{-- Period Filter --}}
            <div>
                <flux:select wire:model.live="period">
                    <option value="today">{{ __('Today') }}</option>
                    <option value="week">{{ __('This Week') }}</option>
                    <option value="month">{{ __('This Month') }}</option>
                    <option value="year">{{ __('This Year') }}</option>
                    <option value="all">{{ __('All Time') }}</option>
                </flux:select>
            </div>
        </div>
    </div>

    {{-- Key Metrics Cards --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total Revenue --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Revenue') }}</flux:text>
                    <flux:heading size="lg" class="mt-2">{{ format_currency($this->totalRevenue) }}
                    </flux:heading>

                    @if ($period !== 'all')
                        <div class="mt-2 flex items-center gap-1 text-sm">
                            @if ($this->revenueGrowth['direction'] === 'up')
                                <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                                <span class="font-medium text-green-600 dark:text-green-400">
                                    +{{ $this->revenueGrowth['percentage'] }}%
                                </span>
                            @else
                                <svg class="h-4 w-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                </svg>
                                <span class="font-medium text-red-600 dark:text-red-400">
                                    {{ $this->revenueGrowth['percentage'] }}%
                                </span>
                            @endif
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('vs last period') }}</span>
                        </div>
                    @endif
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/20">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Total Sales --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Sales') }}</flux:text>
                    <flux:heading size="lg" class="mt-2">{{ number_format($this->totalSales) }}</flux:heading>
                    <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Completed transactions') }}
                    </flux:text>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/20">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Average Order Value --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Avg Order Value') }}</flux:text>
                    <flux:heading size="lg" class="mt-2">{{ format_currency($this->averageOrderValue) }}
                    </flux:heading>
                    <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Per transaction') }}
                    </flux:text>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/20">
                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Total Tax --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Tax') }}</flux:text>
                    <flux:heading size="lg" class="mt-2">{{ format_currency($this->totalTax) }}</flux:heading>
                    <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Tax collected') }}
                    </flux:text>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/20">
                    <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Daily Sales Chart --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">{{ __('Sales Over Time') }}</flux:text>

                @if (count($this->dailySales) > 0)
                    <div class="space-y-3">
                        @php
                            $maxTotal = max(array_column($this->dailySales, 'total'));
                        @endphp
                        @foreach ($this->dailySales as $sale)
                            <div>
                                <div class="mb-1 flex items-center justify-between text-sm">
                                    <flux:text class="font-medium">{{ $sale['date'] }}</flux:text>
                                    <div class="flex items-center gap-2">
                                        <flux:text class="text-zinc-500 dark:text-zinc-400">
                                            {{ $sale['count'] }} {{ __('sales') }}
                                        </flux:text>
                                        <flux:text class="font-medium">{{ format_currency($sale['total']) }}
                                        </flux:text>
                                    </div>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                                    <div class="h-full rounded-full bg-blue-600 dark:bg-blue-500"
                                        style="width: {{ $maxTotal > 0 ? ($sale['total'] / $maxTotal) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center">
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('No sales data available') }}
                        </flux:text>
                    </div>
                @endif
        </div>

        {{-- Payment Methods Breakdown --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">{{ __('Payment Methods') }}</flux:heading>

            @if (count($this->paymentMethodBreakdown) > 0)
                <div class="space-y-4">
                    @foreach ($this->paymentMethodBreakdown as $payment)
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <flux:text class="font-medium">{{ $payment['method'] }}</flux:text>
                                <div class="text-right">
                                    <flux:text class="font-medium">{{ format_currency($payment['total']) }}
                                    </flux:text>
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $payment['count'] }} {{ __('transactions') }}
                                    </flux:text>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                                        <div class="h-full rounded-full bg-green-600 dark:bg-green-500"
                                            style="width: {{ $payment['percentage'] }}%"></div>
                                    </div>
                                </div>
                                <flux:text class="text-sm font-medium">{{ $payment['percentage'] }}%</flux:text>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center">
                    <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('No payment data available') }}
                    </flux:text>
                </div>
            @endif
        </div>

        {{-- Top Selling Products --}}
        <div
            class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800 lg:col-span-2">
            <flux:heading size="lg" class="mb-4">{{ __('Top Selling Products') }}</flux:heading>

            @if (count($this->topProducts) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-zinc-200 dark:border-zinc-700">
                            <tr>
                                <th
                                    class="pb-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Product') }}
                                </th>
                                <th
                                    class="pb-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Quantity Sold') }}
                                </th>
                                <th
                                    class="pb-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Revenue') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($this->topProducts as $product)
                                <tr>
                                    <td class="py-3">
                                        <flux:text class="font-medium">{{ $product->name }}</flux:text>
                                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $product->sku }}
                                        </flux:text>
                                    </td>
                                    <td class="py-3 text-right">
                                        <flux:text class="font-medium">{{ number_format($product->total_quantity) }}
                                        </flux:text>
                                    </td>
                                    <td class="py-3 text-right">
                                        <flux:text class="font-medium">{{ format_currency($product->total_revenue) }}
                                        </flux:text>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-8 text-center">
                    <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('No product sales data available') }}
                    </flux:text>
                </div>
            @endif
        </div>
    </div>
</div>
