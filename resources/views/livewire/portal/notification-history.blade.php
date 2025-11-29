<div>
    <x-layouts.portal-content :customer="$customer" title="Notification History">
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-800 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Total SMS</p>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ $stats['total'] }}</p>
                        </div>
                        <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900/30">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-800 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Delivered</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $stats['sent'] }}
                            </p>
                        </div>
                        <div class="p-3 rounded-full bg-green-100 dark:bg-green-900/30">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-800 p-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">Failed</p>
                                <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $stats['failed'] }}
                                </p>
                            </div>
                            <div class="p-3 rounded-full bg-red-100 dark:bg-red-900/30">
                                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-800 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Pending</p>
                            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">
                                {{ $stats['pending'] }}
                            </p>
                        </div>
                        <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900/30">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search notifications..."
                            icon="magnifying-glass" />
                    </div>
                    <div class="flex gap-2">
                        <flux:button variant="{{ $filter === 'all' ? 'primary' : 'ghost' }}"
                            wire:click="$set('filter', 'all')">
                            All
                        </flux:button>
                        <flux:button variant="{{ $filter === 'sent' ? 'primary' : 'ghost' }}"
                            wire:click="$set('filter', 'sent')">
                            Delivered
                        </flux:button>
                        <flux:button variant="{{ $filter === 'failed' ? 'primary' : 'ghost' }}"
                            wire:click="$set('filter', 'failed')">
                            Failed
                        </flux:button>
                        <flux:button variant="{{ $filter === 'pending' ? 'primary' : 'ghost' }}"
                            wire:click="$set('filter', 'pending')">
                            Pending
                        </flux:button>
                    </div>
                    @if ($search || $filter !== 'all')
                        <flux:button variant="ghost" wire:click="clearFilters" icon="x-mark">
                            Clear
                        </flux:button>
                    @endif
                </div>
            </div>

            <!-- Notification List -->
            <div
                class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                @if ($smsLogs->count() > 0)
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach ($smsLogs as $log)
                            <div class="p-6 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-3 mb-2">
                                            @if ($log->status === 'sent')
                                                <flux:badge color="green" size="sm" icon="check">
                                                    Delivered
                                                </flux:badge>
                                            @elseif($log->status === 'failed')
                                                <flux:badge color="red" size="sm" icon="x-mark">
                                                    Failed
                                                </flux:badge>
                                            @else
                                                <flux:badge color="yellow" size="sm" icon="clock">
                                                    Pending
                                                </flux:badge>
                                            @endif

                                            <span class="text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ $log->created_at->diffForHumans() }}
                                            </span>
                                        </div>

                                        <p class="text-sm text-zinc-900 dark:text-white font-medium mb-1">
                                            {{ Str::afterLast($log->notification_type ?? 'SMS Notification', '\\') }}
                                        </p>

                                        <p class="text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2">
                                            {{ $log->message }}
                                        </p>

                                        @if ($log->error_message)
                                            <div
                                                class="mt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                                <p class="text-sm text-red-700 dark:text-red-400">
                                                    <span class="font-semibold">Error:</span> {{ $log->error_message }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="text-right flex-shrink-0">
                                        <p class="text-xs text-zinc-500 dark:text-zinc-500">
                                            {{ $log->created_at->format('M d, Y') }}
                                        </p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-500">
                                            {{ $log->created_at->format('g:i A') }}
                                        </p>
                                        @if ($log->sent_at)
                                            <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                                                Sent {{ $log->sent_at->format('g:i A') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-800">
                        {{ $smsLogs->links() }}
                    </div>
                @else
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-zinc-400 dark:text-zinc-600 mb-4" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">
                            @if ($search || $filter !== 'all')
                                No notifications found
                            @else
                                No SMS notifications yet
                            @endif
                        </h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            @if ($search || $filter !== 'all')
                                Try adjusting your filters or search terms
                            @else
                                You haven't received any SMS notifications yet
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </x-layouts.portal-content>
</div>
