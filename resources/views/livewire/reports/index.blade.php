<div>
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">{{ __('Reports & Analytics') }}</flux:heading>
        <flux:text>{{ __('Business insights and performance metrics') }}</flux:text>
    </div>

    {{-- Date Range Filter --}}
    <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <flux:field>
                <flux:label>{{ __('Start Date') }}</flux:label>
                <flux:input type="date" wire:model.live="startDate" />
                @error('startDate')
                    <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                @enderror
            </flux:field>

            <flux:field>
                <flux:label>{{ __('End Date') }}</flux:label>
                <flux:input type="date" wire:model.live="endDate" />
                @error('endDate')
                    <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                @enderror
            </flux:field>

            <div class="flex items-end">
                <flux:button wire:click="$set('startDate', '{{ now()->startOfMonth()->format('Y-m-d') }}')"
                    variant="ghost" size="sm">
                    {{ __('This Month') }}
                </flux:button>
            </div>

            <div class="flex items-end">
                <flux:button wire:click="$set('startDate', '{{ now()->subMonth()->startOfMonth()->format('Y-m-d') }}')"
                    variant="ghost" size="sm">
                    {{ __('Last Month') }}
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="mb-6">
        <div class="border-b border-zinc-200 dark:border-zinc-700">
            <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                <button wire:click="$set('tab', 'sales')" type="button"
                    class="{{ $tab === 'sales' ? 'border-emerald-500 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-300' }} whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors">
                    {{ __('Sales Report') }}
                </button>

                <button wire:click="$set('tab', 'payments')" type="button"
                    class="{{ $tab === 'payments' ? 'border-emerald-500 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-300' }} whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors">
                    {{ __('Payment History') }}
                </button>

                <button wire:click="$set('tab', 'pos')" type="button"
                    class="{{ $tab === 'pos' ? 'border-emerald-500 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-300' }} whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors">
                    {{ __('POS Analytics') }}
                </button>

                <button wire:click="$set('tab', 'technicians')" type="button"
                    class="{{ $tab === 'technicians' ? 'border-emerald-500 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-300' }} whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors">
                    {{ __('Technician Performance') }}
                </button>

                <button wire:click="$set('tab', 'inventory')" type="button"
                    class="{{ $tab === 'inventory' ? 'border-emerald-500 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-300' }} whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors">
                    {{ __('Inventory Insights') }}
                </button>
            </nav>
        </div>
    </div>

    {{-- Branch Filter (for tabs that support it) --}}
    @if (in_array($tab, ['sales', 'payments', 'pos']))
        <div class="mb-6">
            <div class="max-w-xs">
                <select wire:model.live="branchFilter"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    <option value="">{{ __('All Branches') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    {{-- Sales Report --}}
    @if ($tab === 'sales')
        <div class="space-y-6">
            {{-- Key Metrics --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ __('Total Revenue') }}
                    </flux:text>
                    <flux:heading size="2xl" class="mt-2">
                        {{ format_currency($totalRevenue) }}
                    </flux:heading>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ __('Transactions') }}
                    </flux:text>
                    <flux:heading size="2xl" class="mt-2">{{ $transactionCount }}</flux:heading>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ __('Avg Transaction') }}
                    </flux:text>
                    <flux:heading size="2xl" class="mt-2">
                        {{ format_currency($avgTransaction) }}
                    </flux:heading>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ __('Pending Invoices') }}
                    </flux:text>
                    <flux:heading size="2xl" class="mt-2 text-amber-600">
                        {{ format_currency($pendingInvoices) }}
                    </flux:heading>
                </div>
            </div>

            {{-- Revenue by Payment Method --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">{{ __('Revenue by Payment Method') }}</flux:heading>
                <div class="space-y-3">
                    @forelse ($revenueByMethod as $method => $amount)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="h-3 w-3 rounded-full bg-blue-500"></div>
                                <flux:text>{{ $method }}</flux:text>
                            </div>
                            <flux:text class="font-semibold">{{ format_currency($amount) }}</flux:text>
                        </div>
                    @empty
                        <flux:text class="text-zinc-500 dark:text-zinc-400">
                            {{ __('No payment data for selected period') }}
                        </flux:text>
                    @endforelse
                </div>
            </div>

            {{-- Daily Revenue Trend --}}
            @if ($dailyRevenue->isNotEmpty())
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:heading size="lg" class="mb-4">{{ __('Daily Revenue Trend') }}</flux:heading>
                    <div class="space-y-2">
                        @foreach ($dailyRevenue as $date => $amount)
                            <div class="flex items-center justify-between">
                                <flux:text class="text-sm">{{ $date }}</flux:text>
                                <flux:text class="font-medium">{{ format_currency($amount) }}</flux:text>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Payment History --}}
    @if ($tab === 'payments')
        <div class="space-y-6">
            {{-- Summary Cards --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ __('Total Collected') }}
                    </flux:text>
                    <flux:heading size="2xl" class="mt-2 text-green-600">
                        {{ format_currency($totalCollected) }}
                    </flux:heading>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ __('Total Payments') }}
                    </flux:text>
                    <flux:heading size="2xl" class="mt-2">{{ $payments->count() }}</flux:heading>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ __('Avg Payment') }}
                    </flux:text>
                    <flux:heading size="2xl" class="mt-2">
                        ${{ $payments->count() > 0 ? number_format($totalCollected / $payments->count(), 2) : '0.00' }}
                    </flux:heading>
                </div>
            </div>

            {{-- Payments by Method --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">{{ __('Payments by Method') }}</flux:heading>
                <div class="space-y-3">
                    @foreach ($paymentsByMethod as $method => $data)
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:text class="font-medium">{{ $method }}</flux:text>
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $data['count'] }} {{ __('payments') }}
                                </flux:text>
                            </div>
                            <flux:text class="font-semibold">{{ format_currency($data['total']) }}</flux:text>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Payment List --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                <div class="p-6">
                    <flux:heading size="lg">{{ __('Recent Payments') }}</flux:heading>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-y border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Date') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Customer') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Amount') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Method') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Processed By') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($payments as $payment)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        {{ $payment->payment_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        {{ $payment->invoice->customer?->full_name }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                        {{ format_currency($payment->amount) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <flux:badge size="sm">{{ $payment->payment_method->label() }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        {{ $payment->processedBy->name }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center">
                                        <flux:text class="text-zinc-500 dark:text-zinc-400">
                                            {{ __('No payments found for selected period') }}
                                        </flux:text>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- POS Analytics --}}
    @if ($tab === 'pos')
        <div class="space-y-6">
            {{-- Key Metrics --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ __('Total POS Revenue') }}
                    </flux:text>
                    <flux:heading size="2xl" class="mt-2 text-emerald-600">
                        {{ format_currency($totalRevenue) }}
                    </flux:heading>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ __('Transactions') }}
                    </flux:text>
                    <flux:heading size="2xl" class="mt-2">{{ number_format($transactionCount) }}</flux:heading>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ __('Avg Transaction') }}
                    </flux:text>
                    <flux:heading size="2xl" class="mt-2">
                        {{ format_currency($avgTransaction) }}
                    </flux:heading>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ __('Items Sold') }}
                    </flux:text>
                    <flux:heading size="2xl" class="mt-2">{{ number_format($totalItemsSold) }}</flux:heading>
                </div>
            </div>

            {{-- Revenue by Payment Method --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">{{ __('Revenue by Payment Method') }}</flux:heading>
                <div class="space-y-4">
                    @forelse ($revenueByMethod as $method => $data)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    @php
                                        $colors = [
                                            'Cash' => 'bg-green-500',
                                            'Card' => 'bg-blue-500',
                                            'Mobile Money' => 'bg-purple-500',
                                            'Bank Transfer' => 'bg-orange-500',
                                        ];
                                        $color = $colors[$method] ?? 'bg-gray-500';
                                    @endphp
                                    <div class="h-3 w-3 rounded-full {{ $color }}"></div>
                                    <flux:text class="font-medium">{{ $method }}</flux:text>
                                    <flux:badge size="sm">{{ $data['count'] }} transactions</flux:badge>
                                </div>
                                <flux:text class="font-semibold text-emerald-600">
                                    {{ format_currency($data['total']) }}
                                </flux:text>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                                <div class="{{ $color }} h-full transition-all"
                                    style="width: {{ $data['percentage'] }}%">
                                </div>
                            </div>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $data['percentage'] }}% of total transactions
                            </flux:text>
                        </div>
                    @empty
                        <flux:text class="text-zinc-500 dark:text-zinc-400">
                            {{ __('No POS sales data for selected period') }}
                        </flux:text>
                    @endforelse
                </div>
            </div>

            {{-- Daily Sales Trend --}}
            @if ($dailySales->isNotEmpty())
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:heading size="lg" class="mb-4">{{ __('Daily Sales Trend') }}</flux:heading>
                    <div class="space-y-3">
                        @php
                            $maxTotal = $dailySales->max('total');
                        @endphp
                        @foreach ($dailySales as $day)
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-sm">
                                    <flux:text class="font-medium">{{ $day['date'] }}</flux:text>
                                    <div class="flex items-center gap-3">
                                        <flux:text class="text-zinc-500 dark:text-zinc-400">
                                            {{ $day['count'] }} sales
                                        </flux:text>
                                        <flux:text class="font-semibold text-emerald-600">
                                            {{ format_currency($day['total']) }}
                                        </flux:text>
                                    </div>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                                    <div class="h-full bg-emerald-500 transition-all"
                                        style="width: {{ $maxTotal > 0 ? ($day['total'] / $maxTotal) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Top Products Grid --}}
            <div class="grid gap-6 lg:grid-cols-2">
                {{-- Top Products by Quantity --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="p-6">
                        <flux:heading size="lg">{{ __('Top Products (Quantity)') }}</flux:heading>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-y border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Product') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Qty Sold') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Revenue') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @forelse ($topProductsByQuantity as $product)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                        <td class="px-6 py-4">
                                            <flux:text class="font-medium">{{ $product->name }}</flux:text>
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $product->sku }}
                                            </flux:text>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold">
                                            {{ number_format($product->total_quantity) }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            {{ format_currency($product->total_revenue) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center">
                                            <flux:text class="text-zinc-500 dark:text-zinc-400">
                                                {{ __('No product data available') }}
                                            </flux:text>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Top Products by Revenue --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="p-6">
                        <flux:heading size="lg">{{ __('Top Products (Revenue)') }}</flux:heading>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-y border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Product') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Revenue') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Qty Sold') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @forelse ($topProductsByRevenue as $product)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                        <td class="px-6 py-4">
                                            <flux:text class="font-medium">{{ $product->name }}</flux:text>
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $product->sku }}
                                            </flux:text>
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-emerald-600">
                                            {{ format_currency($product->total_revenue) }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                            {{ number_format($product->total_quantity) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center">
                                            <flux:text class="text-zinc-500 dark:text-zinc-400">
                                                {{ __('No product data available') }}
                                            </flux:text>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Performance Metrics Grid --}}
            <div class="grid gap-6 lg:grid-cols-2">
                {{-- Sales by Hour --}}
                @if ($salesByHour->isNotEmpty())
                    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:heading size="lg" class="mb-4">{{ __('Sales by Hour') }}</flux:heading>
                        <div class="space-y-2">
                            @php
                                $maxHourlyTotal = $salesByHour->max('total');
                            @endphp
                            @foreach ($salesByHour->take(10) as $hour)
                                <div class="flex items-center gap-3">
                                    <flux:text class="w-16 text-sm font-medium">{{ $hour['hour'] }}</flux:text>
                                    <div class="flex-1">
                                        <div class="h-6 overflow-hidden rounded bg-zinc-100 dark:bg-zinc-700">
                                            <div class="flex h-full items-center bg-emerald-500 px-2 text-xs font-medium text-white transition-all"
                                                style="width: {{ $maxHourlyTotal > 0 ? ($hour['total'] / $maxHourlyTotal) * 100 : 0 }}%">
                                                <span class="whitespace-nowrap">{{ $hour['count'] }} sales</span>
                                            </div>
                                        </div>
                                    </div>
                                    <flux:text class="w-24 text-right text-sm font-semibold">
                                        {{ format_currency($hour['total']) }}
                                    </flux:text>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Sales by Day of Week --}}
                @if ($salesByDayOfWeek->isNotEmpty())
                    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:heading size="lg" class="mb-4">{{ __('Sales by Day of Week') }}</flux:heading>
                        <div class="space-y-3">
                            @foreach ($salesByDayOfWeek as $day => $data)
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between">
                                        <flux:text class="font-medium">{{ $day }}</flux:text>
                                        <div class="flex items-center gap-3 text-sm">
                                            <flux:text class="text-zinc-500 dark:text-zinc-400">
                                                {{ $data['count'] }} sales
                                            </flux:text>
                                            <flux:text class="font-semibold text-emerald-600">
                                                {{ format_currency($data['total']) }}
                                            </flux:text>
                                        </div>
                                    </div>
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                        Avg: {{ format_currency($data['avg']) }} per transaction
                                    </flux:text>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Top Customers --}}
            @if ($topCustomers->isNotEmpty())
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="p-6">
                        <flux:heading size="lg">{{ __('Top Customers') }}</flux:heading>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-y border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Customer') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Transactions') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Total Spent') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Avg Transaction') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($topCustomers as $customerData)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                        <td class="px-6 py-4">
                                            <flux:text class="font-medium">{{ $customerData['customer']?->full_name }}
                                            </flux:text>
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $customerData['customer']?->email }}
                                            </flux:text>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                            {{ $customerData['transactions'] }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-emerald-600">
                                            {{ format_currency($customerData['total_spent']) }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                            {{ format_currency($customerData['avg_transaction']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Additional Metrics --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ __('Total Discounts') }}
                    </flux:text>
                    <flux:heading size="2xl" class="mt-2 text-amber-600">
                        {{ format_currency($totalDiscount) }}
                    </flux:heading>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ __('Cash Sales') }}
                    </flux:text>
                    <flux:heading size="2xl" class="mt-2 text-green-600">
                        {{ format_currency($cashSales) }}
                    </flux:heading>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ __('Card/Digital Sales') }}
                    </flux:text>
                    <flux:heading size="2xl" class="mt-2 text-blue-600">
                        {{ format_currency($cardSales) }}
                    </flux:heading>
                </div>
            </div>
        </div>
    @endif

    {{-- Technician Performance --}}
    @if ($tab === 'technicians')
        <div class="space-y-6">
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                <div class="p-6">
                    <flux:heading size="lg">{{ __('Technician Performance') }}</flux:heading>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-y border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Technician') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Completed') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Active') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Revenue') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Avg Resolution') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($technicians as $data)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                    <td class="px-6 py-4">
                                        <flux:text class="font-medium">{{ $data['technician']->name }}</flux:text>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        {{ $data['completed_tickets'] }} / {{ $data['total_tickets'] }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        {{ $data['active_tickets'] }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                        {{ format_currency($data['revenue']) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        {{ $data['avg_resolution_hours'] }}h
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center">
                                        <flux:text class="text-zinc-500 dark:text-zinc-400">
                                            {{ __('No technician data for selected period') }}
                                        </flux:text>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Inventory Insights --}}
    @if ($tab === 'inventory')
        <div class="space-y-6">
            {{-- Summary Cards --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ __('Inventory Value (Cost)') }}
                    </flux:text>
                    <flux:heading size="2xl" class="mt-2">
                        {{ format_currency($totalInventoryValue) }}
                    </flux:heading>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ __('Retail Value') }}
                    </flux:text>
                    <flux:heading size="2xl" class="mt-2">
                        {{ format_currency($totalRetailValue) }}
                    </flux:heading>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ __('Low Stock Items') }}
                    </flux:text>
                    <flux:heading size="2xl" class="mt-2 text-red-600">
                        {{ $lowStockItems->count() }}
                    </flux:heading>
                </div>
            </div>

            {{-- Most Used Parts --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                <div class="p-6">
                    <flux:heading size="lg">{{ __('Most Used Parts') }}</flux:heading>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-y border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Part') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('SKU') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Quantity Used') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Revenue') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($mostUsedParts as $part)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                    <td class="px-6 py-4">
                                        <flux:text class="font-medium">{{ $part->name }}</flux:text>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        {{ $part->sku }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        {{ $part->total_quantity }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                        {{ format_currency($part->total_revenue) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center">
                                        <flux:text class="text-zinc-500 dark:text-zinc-400">
                                            {{ __('No parts usage data available') }}
                                        </flux:text>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Low Stock Items --}}
            @if ($lowStockItems->isNotEmpty())
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="p-6">
                        <flux:heading size="lg">{{ __('Low Stock Alert') }}</flux:heading>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-y border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Item') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Current Stock') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Reorder Level') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($lowStockItems as $item)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                        <td class="px-6 py-4">
                                            <flux:text class="font-medium">{{ $item->name }}</flux:text>
                                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $item->sku }}
                                            </flux:text>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <flux:badge color="red" size="sm">{{ $item->quantity }}
                                            </flux:badge>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            {{ $item->reorder_level }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
