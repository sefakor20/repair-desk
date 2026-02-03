<div class="max-w-2xl space-y-6">
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">Open Shift</flux:heading>
        <flux:text>Start a new shift to begin tracking sales</flux:text>
    </div>

    <form wire:submit="open" class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="space-y-4">
                <flux:field>
                    <flux:label>Shift Name</flux:label>
                    <flux:input wire:model="shift_name" type="text" placeholder="e.g., Morning Shift, Evening Shift"
                        required />
                    <flux:error name="shift_name" />
                    <flux:description>A descriptive name for this shift period</flux:description>
                </flux:field>

                <flux:field>
                    <flux:label>Opening Notes (Optional)</flux:label>
                    <flux:textarea wire:model="opening_notes" rows="3"
                        placeholder="Any notes about this shift..." />
                    <flux:error name="opening_notes" />
                </flux:field>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <flux:button variant="ghost" :href="route('shifts.index')" wire:navigate>
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary" icon="lock-open">
                Open Shift
            </flux:button>
        </div>
    </form>
</div>
