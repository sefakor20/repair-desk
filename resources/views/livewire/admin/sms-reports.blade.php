<div>
    {{-- Header --}}
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">SMS Cost Reports</flux:heading>
        <flux:subheading>Analyze SMS costs, trends, and usage patterns</flux:subheading>
    </div>

    {{-- Filters --}}
    <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid gap-4 md:grid-cols-5">
            <div>
                <flux:input type="date" wire:model.live="dateFrom" placeholder="From Date" label="From Date" />
            </div>

            <div>
                <flux:input type="date" wire:model.live="dateTo" placeholder="To Date" label="To Date" />
            </div>

            <div>
                <flux:select wire:model.live="groupBy" label="Group By">
                    <option value="day">Daily</option>
                    <option value="week">Weekly</option>
                    <option value="month">Monthly</option>
                </flux:select>
            </div>

            <div>
                <flux:select wire:model.live="notificationType" label="Type">
                    <option value="all">All Types</option>
                    @foreach ($costByType as $type)
                        <option value="{{ $type->notification_type }}">
                            {{ class_basename($type->notification_type ?? 'N/A') }}
                        </option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex items-end">
                <flux:button variant="primary" size="sm" wire:click="export" icon="arrow-down-tray" class="w-full">
                    Export Report
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Summary Statistics --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <flux:subheading class="text-zinc-600 dark:text-zinc-400">Total Cost</flux:subheading>
                    <flux:heading size="2xl" class="mt-2">${{ number_format($stats['total_cost'], 2) }}
                    </flux:heading>
                    <div class="mt-1 text-xs text-zinc-500">
                        ${{ number_format($stats['avg_cost_per_message'], 4) }} per message
                    </div>
                </div>
                <div class="rounded-lg bg-emerald-100 p-3 dark:bg-emerald-900/20">
                    <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <flux:subheading class="text-zinc-600 dark:text-zinc-400">Total Messages</flux:subheading>
                    <flux:heading size="2xl" class="mt-2">{{ number_format($stats['total_messages']) }}
                    </flux:heading>
                    <div class="mt-1 text-xs text-zinc-500">
                        {{ number_format($stats['total_segments']) }} segments
                    </div>
                </div>
                <div class="rounded-lg bg-purple-100 p-3 dark:bg-purple-900/20">
                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <flux:subheading class="text-zinc-600 dark:text-zinc-400">Success Rate</flux:subheading>
                    <flux:heading size="2xl" class="mt-2">{{ $stats['success_rate'] }}%</flux:heading>
                    <div class="mt-1 text-xs text-zinc-500">
                        {{ number_format($stats['sent_messages']) }} delivered
                    </div>
                </div>
                <div class="rounded-lg bg-blue-100 p-3 dark:bg-blue-900/20">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <flux:subheading class="text-zinc-600 dark:text-zinc-400">Failed Messages</flux:subheading>
                    <flux:heading size="2xl" class="mt-2">{{ number_format($stats['failed_messages']) }}
                    </flux:heading>
                    <div class="mt-1 text-xs text-zinc-500">
                        {{ $stats['total_messages'] > 0 ? round(($stats['failed_messages'] / $stats['total_messages']) * 100, 1) : 0 }}%
                        failure rate
                    </div>
                </div>
                <div class="rounded-lg bg-red-100 p-3 dark:bg-red-900/20">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Grid --}}
    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        {{-- Cost Over Time Chart --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:heading size="lg" class="mb-4">Cost Over Time</flux:heading>
            <div class="space-y-3">
                @php
                    $maxCost = $costOverTime->max('total_cost') ?: 1;
                @endphp
                @forelse($costOverTime as $data)
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">{{ $data->date }}</span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">
                                ${{ number_format($data->total_cost, 2) }}
                            </span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
                            <div class="h-full bg-emerald-500 transition-all"
                                style="width: {{ ($data->total_cost / $maxCost) * 100 }}%"></div>
                        </div>
                        <div class="mt-1 text-xs text-zinc-500">
                            {{ $data->total_messages }} messages, {{ $data->total_segments }} segments
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-zinc-500">
                        No data available for selected period
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Cost by Type --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:heading size="lg" class="mb-4">Cost by Notification Type</flux:heading>
            <div class="space-y-3">
                @php
                    $maxTypeCost = $costByType->max('total_cost') ?: 1;
                @endphp
                @forelse($costByType as $type)
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">
                                {{ class_basename($type->notification_type ?? 'N/A') }}
                            </span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">
                                ${{ number_format($type->total_cost, 2) }}
                            </span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
                            <div class="h-full bg-purple-500 transition-all"
                                style="width: {{ ($type->total_cost / $maxTypeCost) * 100 }}%"></div>
                        </div>
                        <div class="mt-1 text-xs text-zinc-500">
                            {{ $type->total_messages }} messages, {{ $type->total_segments }} segments
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-zinc-500">
                        No data available for selected period
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Bottom Grid --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Cost by Status --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:heading size="lg" class="mb-4">Cost by Status</flux:heading>
            <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-800">
                <table class="w-full">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase text-zinc-500">Status</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase text-zinc-500">Messages</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase text-zinc-500">Cost</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase text-zinc-500">Segments</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($costByStatus as $status)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3">
                                    @if ($status->status === 'sent')
                                        <flux:badge color="green" size="sm">{{ ucfirst($status->status) }}
                                        </flux:badge>
                                    @elseif($status->status === 'failed')
                                        <flux:badge color="red" size="sm">{{ ucfirst($status->status) }}
                                        </flux:badge>
                                    @else
                                        <flux:badge color="amber" size="sm">{{ ucfirst($status->status) }}
                                        </flux:badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-zinc-900 dark:text-zinc-100">
                                    {{ number_format($status->total_messages) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    ${{ number_format($status->total_cost, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ number_format($status->total_segments) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-zinc-500">
                                    No data available
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top Cost Days --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:heading size="lg" class="mb-4">Top 5 Highest Cost Days</flux:heading>
            <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-800">
                <table class="w-full">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase text-zinc-500">Date</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase text-zinc-500">Messages</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase text-zinc-500">Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($topCostDays as $day)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                    {{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ number_format($day->total_messages) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    ${{ number_format($day->total_cost, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-zinc-500">
                                    No data available
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
