<div class="mx-auto max-w-2xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">Open Cash Drawer</flux:heading>
        <flux:text>Enter the starting cash amount to begin a new session</flux:text>
    </div>

    <form wire:submit="open" class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="space-y-4">
                <flux:field>
                    <flux:label>Opening Balance (GHS)</flux:label>
                    <flux:input wire:model="opening_balance" type="number" step="0.01" min="0"
                        placeholder="0.00" required />
                    <flux:error name="opening_balance" />
                    <flux:description>Count all cash in the drawer before opening</flux:description>
                </flux:field>

                <flux:field>
                    <flux:label>Opening Notes (Optional)</flux:label>
                    <flux:textarea wire:model="opening_notes" rows="3"
                        placeholder="Any notes about this opening session..." />
                    <flux:error name="opening_notes" />
                </flux:field>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <flux:button variant="ghost" :href="route('cash-drawer.index')" wire:navigate>
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary" icon="lock-open">
                Open Drawer
            </flux:button>
        </div>
    </form>
</div>
