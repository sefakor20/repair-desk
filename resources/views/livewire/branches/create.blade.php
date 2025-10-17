<div>
    {{-- If your happiness depends on money, you will never be happy with yourself. --}}
</div>

<x-flux:modal title="Create Branch">
    <form wire:submit.prevent="save" class="space-y-6">
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
            <flux:button type="submit" variant="primary">Create Branch</flux:button>
            <flux:button type="button" variant="secondary" wire:click="$router.back()">Cancel</flux:button>
        </div>
    </form>
</x-flux:modal>
