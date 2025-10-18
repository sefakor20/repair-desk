<div class="max-w-2xl space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Edit Branch</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Update branch details and location information.</p>
    </div>
    <form wire:submit.prevent="save"
        class="space-y-6 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6 shadow">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:field label="Name" required>
                <flux:input wire:model.defer="form.name" required />
            </flux:field>
            <flux:field label="Code" required>
                <flux:input wire:model.defer="form.code" required maxlength="10" />
            </flux:field>
            <flux:field label="City">
                <flux:input wire:model.defer="form.city" />
            </flux:field>
            <flux:field label="State">
                <flux:input wire:model.defer="form.state" />
            </flux:field>
            <flux:field label="Country">
                <flux:input wire:model.defer="form.country" />
            </flux:field>
            <flux:field label="Zip">
                <flux:input wire:model.defer="form.zip" />
            </flux:field>
            <flux:field label="Phone">
                <flux:input wire:model.defer="form.phone" />
            </flux:field>
            <flux:field label="Email">
                <flux:input wire:model.defer="form.email" type="email" />
            </flux:field>
        </div>
        <flux:field label="Address">
            <flux:textarea wire:model.defer="form.address" />
        </flux:field>
        <div class="flex gap-4">
            <flux:switch wire:model.defer="form.is_active" label="Active" />
            <flux:switch wire:model.defer="form.is_main" label="Main Branch" />
        </div>
        <flux:field label="Notes">
            <flux:textarea wire:model.defer="form.notes" />
        </flux:field>
        <div class="flex justify-end gap-2 mt-6">
            <button type="button" onclick="window.history.back()"
                class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                Cancel
            </button>
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-zinc-800 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 dark:focus:ring-white">
                Update Branch
            </button>
        </div>
    </form>
</div>
