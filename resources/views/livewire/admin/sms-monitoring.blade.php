<div>
    {{-- Header --}}
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">SMS Monitoring</flux:heading>
        <flux:subheading>Monitor SMS delivery status and track communication with customers</flux:subheading>
    </div>

    {{-- Statistics Cards --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <flux:subheading class="text-zinc-600 dark:text-zinc-400">Total SMS</flux:subheading>
                    <flux:heading size="2xl" class="mt-2">{{ number_format($stats['total']) }}</flux:heading>
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
                    <flux:subheading class="text-zinc-600 dark:text-zinc-400">Delivered</flux:subheading>
                    <flux:heading size="2xl" class="mt-2">{{ number_format($stats['sent']) }}</flux:heading>
                </div>
                <div class="rounded-lg bg-green-100 p-3 dark:bg-green-900/20">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24"
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
                    <flux:subheading class="text-zinc-600 dark:text-zinc-400">Failed</flux:subheading>
                    <flux:heading size="2xl" class="mt-2">{{ number_format($stats['failed']) }}</flux:heading>
                </div>
                <div class="rounded-lg bg-red-100 p-3 dark:bg-red-900/20">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <flux:subheading class="text-zinc-600 dark:text-zinc-400">Pending</flux:subheading>
                    <flux:heading size="2xl" class="mt-2">{{ number_format($stats['pending']) }}</flux:heading>
                </div>
                <div class="rounded-lg bg-amber-100 p-3 dark:bg-amber-900/20">
                    <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <flux:subheading class="text-zinc-600 dark:text-zinc-400">Success Rate</flux:subheading>
                    <flux:heading size="2xl" class="mt-2">{{ $successRate }}%</flux:heading>
                </div>
                <div class="rounded-lg bg-blue-100 p-3 dark:bg-blue-900/20">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid gap-4 md:grid-cols-5">
            <div class="md:col-span-2">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search phone, message, or type..."
                    icon="magnifying-glass" />
            </div>

            <div>
                <flux:select wire:model.live="statusFilter">
                    <option value="all">All Status</option>
                    <option value="sent">Sent</option>
                    <option value="failed">Failed</option>
                    <option value="pending">Pending</option>
                </flux:select>
            </div>

            <div>
                <flux:input type="date" wire:model.live="dateFrom" placeholder="From Date" />
            </div>

            <div>
                <flux:input type="date" wire:model.live="dateTo" placeholder="To Date" />
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between">
            <flux:button variant="ghost" size="sm" wire:click="clearFilters" icon="x-mark">
                Clear Filters
            </flux:button>

            <flux:button variant="primary" size="sm" wire:click="export" icon="arrow-down-tray">
                Export CSV
            </flux:button>
        </div>
    </div>

    {{-- SMS Logs Table --}}
    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">
                            Phone
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">
                            Message</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">
                            Customer</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $log->created_at->format('M d, Y H:i') }}
                            </td>
                            <td
                                class="whitespace-nowrap px-6 py-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $log->phone }}
                            </td>
                            <td class="max-w-md px-6 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                                <div class="line-clamp-2">{{ $log->message }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-xs text-zinc-500">
                                {{ class_basename($log->notification_type ?? 'N/A') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @if ($log->status === 'sent')
                                    <flux:badge color="green" size="sm">Sent</flux:badge>
                                @elseif($log->status === 'failed')
                                    <flux:badge color="red" size="sm">Failed</flux:badge>
                                @else
                                    <flux:badge color="amber" size="sm">Pending</flux:badge>
                                @endif

                                @if ($log->error_message)
                                    <div class="mt-1 text-xs text-red-600 dark:text-red-400">
                                        {{ Str::limit($log->error_message, 50) }}
                                    </div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                                @if ($log->notifiable)
                                    {{ $log->notifiable->first_name ?? 'N/A' }}
                                    {{ $log->notifiable->last_name ?? '' }}
                                @else
                                    <span class="text-zinc-400">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                                <flux:heading size="lg" class="mt-4 text-zinc-900 dark:text-zinc-100">No SMS logs
                                    found
                                </flux:heading>
                                <flux:subheading class="mt-2">
                                    @if ($search || $statusFilter !== 'all')
                                        Try adjusting your filters to see more results.
                                    @else
                                        SMS delivery logs will appear here once messages are sent.
                                    @endif
                                </flux:subheading>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($logs->hasPages())
            <div class="border-t border-zinc-200 bg-white px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
