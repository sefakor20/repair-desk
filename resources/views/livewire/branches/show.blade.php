<x-flux:modal title="Branch Details">
    <div class="space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:gap-8">
            <div class="flex-1">
                <flux:heading>{{ $branch->name }}</flux:heading>
                <div class="text-sm text-gray-500">Code: {{ $branch->code }}</div>
                <div class="text-sm text-gray-500">{{ $branch->full_address }}</div>
                <div class="text-sm text-gray-500">Phone: {{ $branch->phone }}</div>
                <div class="text-sm text-gray-500">Email: {{ $branch->email }}</div>
                <div class="text-sm text-gray-500">Status: <span
                        class="font-semibold">{{ $branch->is_active ? 'Active' : 'Inactive' }}</span></div>
                <div class="text-sm text-gray-500">Main Branch: <span
                        class="font-semibold">{{ $branch->is_main ? 'Yes' : 'No' }}</span></div>
            </div>
            <div class="flex flex-col gap-2 mt-4 md:mt-0">
                <flux:badge variant="primary">Users: {{ $branch->users_count }}</flux:badge>
                <flux:badge variant="info">Tickets: {{ $branch->tickets_count }}</flux:badge>
                <flux:badge variant="success">Inventory: {{ $branch->inventory_items_count }}</flux:badge>
                <flux:badge variant="warning">POS Sales: {{ $branch->pos_sales_count }}</flux:badge>
            </div>
        </div>
        <div class="mt-4">
            <flux:callout variant="info">
                <div>{{ $branch->notes }}</div>
            </flux:callout>
        </div>
        <div class="flex justify-end gap-2 mt-6">
            <flux:button variant="secondary" wire:click="$router.back()">Close</flux:button>
            <flux:button variant="primary" :href="route('branches.edit', $branch)">Edit Branch</flux:button>
        </div>
    </div>
</x-flux:modal>
