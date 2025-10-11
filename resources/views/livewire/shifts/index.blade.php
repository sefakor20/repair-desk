<div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" class="mb-2">Shifts</flux:heading>
            <flux:text>Manage employee shifts and track sales performance</flux:text>
        </div>

        @if ($activeShift)
            <flux:button variant="danger" :href="route('shifts.close')" wire:navigate icon="lock-closed">
                Close Shift
            </flux:button>
        @else
            <flux:button variant="primary" :href="route('shifts.open')" wire:navigate icon="lock-open">
                Open Shift
            </flux:button>
        @endif
    </div>

    <!-- Active Shift Card -->
    @if ($activeShift)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-6 dark:border-emerald-800 dark:bg-emerald-950">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <flux:badge color="green" size="sm">Active Shift</flux:badge>
                    <flux:text class="font-semibold">{{ $activeShift->shift_name }}</flux:text>
                </div>
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                    Started {{ $activeShift->started_at->format('M d, Y g:i A') }}
                </flux:text>
            </div>

            <div class="grid gap-4 sm:grid-cols-4">
                <div>
                    <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Total Sales</flux:text>
                    <flux:text class="text-2xl font-bold">GHS {{ number_format($activeShift->total_sales, 2) }}
                    </flux:text>
                </div>
                <div>
                    <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Sales Count</flux:text>
                    <flux:text class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                        {{ number_format($activeShift->sales_count) }}
                    </flux:text>
                </div>
                <div>
                    <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Average Sale</flux:text>
                    <flux:text class="text-2xl font-bold">GHS {{ number_format($activeShift->averageSaleAmount(), 2) }}
                    </flux:text>
                </div>
                <div>
                    <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Duration</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $activeShift->started_at->diffForHumans(now(), true) }}
                    </flux:text>
                </div>
            </div>
        </div>
    @endif

    <!-- Search -->
    <div class="flex items-center gap-4">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search shifts..."
                icon="magnifying-glass" />
        </div>
    </div>

    <!-- Shifts List -->
    @if ($shifts->isEmpty())
        <div class="rounded-xl border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <flux:icon.clock variant="outline" class="mx-auto mb-4 size-12 text-zinc-400" />
            <flux:heading size="lg" class="mb-2">No shifts recorded</flux:heading>
            <flux:text class="mb-6">Open a new shift to start tracking sales.</flux:text>
            <flux:button variant="primary" :href="route('shifts.open')" wire:navigate>Open Shift</flux:button>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($shifts as $shift)
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="mb-4 flex items-start justify-between">
                        <div class="flex-1">
                            <div class="mb-2 flex items-center gap-3">
                                <flux:badge :color="$shift->status->color()" size="sm">
                                    {{ $shift->status->label() }}
                                </flux:badge>
                                <flux:heading size="lg">{{ $shift->shift_name }}</flux:heading>
                            </div>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                Opened by {{ $shift->openedBy->name }} •
                                {{ $shift->started_at->format('M d, Y g:i A') }}
                                @if ($shift->ended_at)
                                    • Closed {{ $shift->ended_at->format('M d, Y g:i A') }}
                                    @if ($shift->duration())
                                        ({{ floor($shift->duration() / 60) }}h {{ $shift->duration() % 60 }}m)
                                    @endif
                                @endif
                            </flux:text>
                        </div>
                    </div>

                    <div class="grid gap-4 border-t border-zinc-200 pt-4 dark:border-zinc-700 sm:grid-cols-5">
                        <div>
                            <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Total Sales</flux:text>
                            <flux:text class="font-semibold">GHS {{ number_format($shift->total_sales, 2) }}
                            </flux:text>
                        </div>
                        <div>
                            <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Sales Count</flux:text>
                            <flux:text class="font-semibold">{{ $shift->sales_count }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Cash</flux:text>
                            <flux:text class="font-semibold text-emerald-600 dark:text-emerald-400">
                                GHS {{ number_format($shift->cash_sales, 2) }}
                            </flux:text>
                        </div>
                        <div>
                            <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Card</flux:text>
                            <flux:text class="font-semibold text-blue-600 dark:text-blue-400">
                                GHS {{ number_format($shift->card_sales, 2) }}
                            </flux:text>
                        </div>
                        <div>
                            <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Mobile Money</flux:text>
                            <flux:text class="font-semibold text-amber-600 dark:text-amber-400">
                                GHS {{ number_format($shift->mobile_money_sales, 2) }}
                            </flux:text>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $shifts->links() }}
        </div>
    @endif
</div>
