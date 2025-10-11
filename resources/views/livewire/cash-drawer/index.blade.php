<div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" class="mb-2">Cash Drawer</flux:heading>
            <flux:text>Manage cash drawer sessions and reconciliation</flux:text>
        </div>

        @if ($activeSession)
            <flux:button variant="danger" href="#" wire:navigate icon="lock-closed">
                Close Drawer
            </flux:button>
        @else
            <flux:button variant="primary" href="#" wire:navigate icon="lock-open">
                Open Drawer
            </flux:button>
        @endif
    </div>

    <!-- Active Session Card -->
    @if ($activeSession)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-6 dark:border-emerald-800 dark:bg-emerald-950">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <flux:badge color="green" size="sm">Active Session</flux:badge>
                    <flux:text class="font-semibold">Opened by {{ $activeSession->openedBy->name }}</flux:text>
                </div>
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ $activeSession->opened_at->format('M d, Y g:i A') }}
                </flux:text>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Opening Balance</flux:text>
                    <flux:text class="text-2xl font-bold">GHS {{ number_format($activeSession->opening_balance, 2) }}
                    </flux:text>
                </div>
                <div>
                    <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Cash Sales</flux:text>
                    <flux:text class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                        +GHS {{ number_format($activeSession->cash_sales, 2) }}
                    </flux:text>
                </div>
                <div>
                    <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Expected Balance</flux:text>
                    <flux:text class="text-2xl font-bold">GHS
                        {{ number_format($activeSession->calculateExpectedBalance(), 2) }}</flux:text>
                </div>
            </div>
        </div>
    @endif

    <!-- Search -->
    <div class="flex items-center gap-4">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search sessions..."
                icon="magnifying-glass" />
        </div>
    </div>

    <!-- Sessions List -->
    @if ($sessions->isEmpty())
        <div class="rounded-xl border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <flux:icon.banknotes variant="outline" class="mx-auto mb-4 size-12 text-zinc-400" />
            <flux:heading size="lg" class="mb-2">No cash drawer sessions</flux:heading>
            <flux:text class="mb-6">Open a new cash drawer session to start tracking cash.</flux:text>
            <flux:button variant="primary" href="#" wire:navigate>Open Drawer</flux:button>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($sessions as $session)
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="mb-4 flex items-start justify-between">
                        <div class="flex-1">
                            <div class="mb-2 flex items-center gap-3">
                                <flux:badge :color="$session->status->color()" size="sm">
                                    {{ $session->status->label() }}
                                </flux:badge>
                                <flux:text class="font-semibold">{{ $session->openedBy->name }}</flux:text>
                            </div>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                Opened {{ $session->opened_at->format('M d, Y g:i A') }}
                                @if ($session->closed_at)
                                    • Closed {{ $session->closed_at->format('M d, Y g:i A') }}
                                @endif
                            </flux:text>
                        </div>
                    </div>

                    <div class="grid gap-4 border-t border-zinc-200 pt-4 dark:border-zinc-700 sm:grid-cols-4">
                        <div>
                            <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Opening</flux:text>
                            <flux:text class="font-semibold">GHS {{ number_format($session->opening_balance, 2) }}
                            </flux:text>
                        </div>
                        <div>
                            <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Expected</flux:text>
                            <flux:text class="font-semibold">GHS
                                {{ number_format($session->expected_balance ?? $session->calculateExpectedBalance(), 2) }}
                            </flux:text>
                        </div>
                        @if ($session->actual_balance !== null)
                            <div>
                                <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Actual</flux:text>
                                <flux:text class="font-semibold">GHS {{ number_format($session->actual_balance, 2) }}
                                </flux:text>
                            </div>
                            <div>
                                <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Discrepancy</flux:text>
                                <flux:text class="font-semibold"
                                    :class="$session->discrepancy > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($session->discrepancy < 0 ? 'text-red-600 dark:text-red-400' : '')">
                                    {{ $session->discrepancy > 0 ? '+' : '' }}GHS
                                    {{ number_format($session->discrepancy, 2) }}
                                </flux:text>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $sessions->links() }}
        </div>
    @endif
</div>
