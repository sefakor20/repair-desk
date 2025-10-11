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
        <flux:navlist variant="outline">
            <flux:navlist.item wire:click="$set('tab', 'sales')" :current="$tab === 'sales'">
                {{ __('Sales Report') }}
            </flux:navlist.item>
            <flux:navlist.item wire:click="$set('tab', 'payments')" :current="$tab === 'payments'">
                {{ __('Payment History') }}
            </flux:navlist.item>
            <flux:navlist.item wire:click="$set('tab', 'technicians')" :current="$tab === 'technicians'">
                {{ __('Technician Performance') }}
            </flux:navlist.item>
            <flux:navlist.item wire:click="$set('tab', 'inventory')" :current="$tab === 'inventory'">
                {{ __('Inventory Insights') }}
            </flux:navlist.item>
        </flux:navlist>
    </div>

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
                        ${{ number_format($totalRevenue, 2) }}
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
                        ${{ number_format($avgTransaction, 2) }}
                    </flux:heading>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ __('Pending Invoices') }}
                    </flux:text>
                    <flux:heading size="2xl" class="mt-2 text-amber-600">
                        ${{ number_format($pendingInvoices, 2) }}
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
                            <flux:text class="font-semibold">${{ number_format($amount, 2) }}</flux:text>
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
                                <flux:text class="font-medium">${{ number_format($amount, 2) }}</flux:text>
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
                        ${{ number_format($totalCollected, 2) }}
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
                            <flux:text class="font-semibold">${{ number_format($data['total'], 2) }}</flux:text>
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
                                        {{ $payment->invoice->customer->full_name }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                        ${{ number_format($payment->amount, 2) }}
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
                                        ${{ number_format($data['revenue'], 2) }}
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
                        ${{ number_format($totalInventoryValue, 2) }}
                    </flux:heading>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ __('Retail Value') }}
                    </flux:text>
                    <flux:heading size="2xl" class="mt-2">
                        ${{ number_format($totalRetailValue, 2) }}
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
                                        ${{ number_format($part->total_revenue, 2) }}
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
