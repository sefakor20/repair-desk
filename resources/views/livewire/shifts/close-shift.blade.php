<div class="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">Close Shift</flux:heading>
        <flux:text>Review your shift performance and close</flux:text>
    </div>

    <form wire:submit="close" class="space-y-6">
        <!-- Shift Summary -->
        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="lg">{{ $shift->shift_name }}</flux:heading>
                <flux:badge color="green" size="sm">Active</flux:badge>
            </div>

            <flux:text class="mb-6 text-sm text-zinc-600 dark:text-zinc-400">
                Started {{ $shift->started_at->format('M d, Y g:i A') }} • Duration: {{ $duration }}
            </flux:text>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Total Sales</flux:text>
                    <flux:text class="text-2xl font-bold">GHS {{ number_format($shift->total_sales, 2) }}</flux:text>
                </div>
                <div>
                    <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Sales Count</flux:text>
                    <flux:text class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                        {{ $shift->sales_count }}
                    </flux:text>
                </div>
                <div>
                    <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Average Sale</flux:text>
                    <flux:text class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        GHS {{ number_format($averageSaleAmount, 2) }}
                    </flux:text>
                </div>
            </div>

            <!-- Payment Methods Breakdown -->
            <div class="mt-6 border-t border-zinc-200 pt-6 dark:border-zinc-700">
                <flux:text class="mb-4 font-semibold">Payment Methods Breakdown</flux:text>
                <div class="grid gap-4 sm:grid-cols-4">
                    <div>
                        <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Cash</flux:text>
                        <flux:text class="text-lg font-semibold text-emerald-600 dark:text-emerald-400">
                            GHS {{ number_format($shift->cash_sales, 2) }}
                        </flux:text>
                    </div>
                    <div>
                        <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Card</flux:text>
                        <flux:text class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                            GHS {{ number_format($shift->card_sales, 2) }}
                        </flux:text>
                    </div>
                    <div>
                        <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Mobile Money</flux:text>
                        <flux:text class="text-lg font-semibold text-amber-600 dark:text-amber-400">
                            GHS {{ number_format($shift->mobile_money_sales, 2) }}
                        </flux:text>
                    </div>
                    <div>
                        <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Bank Transfer</flux:text>
                        <flux:text class="text-lg font-semibold text-purple-600 dark:text-purple-400">
                            GHS {{ number_format($shift->bank_transfer_sales, 2) }}
                        </flux:text>
                    </div>
                </div>
            </div>
        </div>

        <!-- Closing Notes -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:field>
                <flux:label>Closing Notes (Optional)</flux:label>
                <flux:textarea wire:model="closing_notes" rows="3"
                    placeholder="Any notes about this shift, issues, or handover details..." />
                <flux:error name="closing_notes" />
            </flux:field>
        </div>

        <div class="flex items-center justify-between">
            <flux:button variant="ghost" :href="route('shifts.index')" wire:navigate>
                Cancel
            </flux:button>
            <flux:button type="submit" variant="danger" icon="lock-closed">
                Close Shift
            </flux:button>
        </div>
    </form>
</div>
