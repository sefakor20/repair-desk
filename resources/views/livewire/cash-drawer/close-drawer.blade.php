<div class="mx-auto max-w-2xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">Close Cash Drawer</flux:heading>
        <flux:text>Count the cash and reconcile your drawer</flux:text>
    </div>

    <form wire:submit="close" class="space-y-6">
        <!-- Session Summary -->
        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">Session Summary</flux:heading>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Opening Balance</flux:text>
                    <flux:text class="text-xl font-semibold">{{ format_currency($session->opening_balance) }}
                    </flux:text>
                </div>
                <div>
                    <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Cash Sales</flux:text>
                    <flux:text class="text-xl font-semibold text-emerald-600 dark:text-emerald-400">
                        +{{ format_currency($session->cash_sales) }}
                    </flux:text>
                </div>
                <div>
                    <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Cash In</flux:text>
                    <flux:text class="text-xl font-semibold text-blue-600 dark:text-blue-400">
                        +{{ format_currency($session->cash_in) }}
                    </flux:text>
                </div>
                <div>
                    <flux:text class="mb-1 text-sm text-zinc-600 dark:text-zinc-400">Cash Out</flux:text>
                    <flux:text class="text-xl font-semibold text-red-600 dark:text-red-400">
                        -{{ format_currency($session->cash_out) }}
                    </flux:text>
                </div>
            </div>

            <div class="mt-6 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <flux:text class="mb-2 text-sm text-zinc-600 dark:text-zinc-400">Expected Balance</flux:text>
                <flux:text class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">
                    {{ format_currency($expectedBalance) }}
                </flux:text>
            </div>
        </div>

        <!-- Actual Count -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="space-y-4">
                <flux:field>
                    <flux:label>Actual Cash Count (GHS)</flux:label>
                    <flux:input wire:model.live="actual_balance" type="number" step="0.01" min="0"
                        placeholder="0.00" required />
                    <flux:error name="actual_balance" />
                    <flux:description>Count all physical cash currently in the drawer</flux:description>
                </flux:field>

                <!-- Discrepancy Alert -->
                @if (abs($discrepancy) > 0.01)
                    <div
                        class="rounded-lg border p-4 @if ($discrepancy > 0) border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-950 @else border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950 @endif">
                        <div class="flex items-center gap-2">
                            <flux:icon.exclamation-triangle class="size-5" />
                            <div class="flex-1">
                                <flux:text class="font-semibold">
                                    {{ $discrepancy > 0 ? 'Overage' : 'Shortage' }} Detected
                                </flux:text>
                                <flux:text class="text-sm">
                                    Discrepancy: {{ $discrepancy > 0 ? '+' : '' }}GHS
                                    {{ number_format($discrepancy, 2) }}
                                </flux:text>
                            </div>
                        </div>
                    </div>
                @endif

                <flux:field>
                    <flux:label>Closing Notes (Optional)</flux:label>
                    <flux:textarea wire:model="closing_notes" rows="3"
                        placeholder="Any notes about discrepancies or issues..." />
                    <flux:error name="closing_notes" />
                </flux:field>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <flux:button variant="ghost" :href="route('cash-drawer.index')" wire:navigate>
                Cancel
            </flux:button>
            <flux:button type="submit" variant="danger" icon="lock-closed">
                Close Drawer
            </flux:button>
        </div>
    </form>
</div>
