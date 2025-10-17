<x-flux:modal title="Branches">
    <div class="flex flex-col gap-4">
        <div class="flex flex-col md:flex-row md:items-center md:gap-4 gap-2">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search branches..." class="w-full md:w-1/3" />
            <flux:select wire:model="statusFilter"
                :options="[
                    ['', 'All'],
                    ['active', 'Active'],
                    ['inactive', 'Inactive']
                ]" class="w-full md:w-1/6" />
            <flux:button variant="primary" :href="route('branches.create')">New Branch</flux:button>
            <flux:button variant="secondary" wire:click="clearFilters">Clear Filters</flux:button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Code</th>
                        <th class="px-4 py-2">City</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Main</th>
                        <th class="px-4 py-2">Users</th>
                        <th class="px-4 py-2">Tickets</th>
                        <th class="px-4 py-2">Inventory</th>
                        <th class="px-4 py-2">POS Sales</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($branches as $branch)
                        <tr>
                            <td class="px-4 py-2 font-semibold">
                                <a href="{{ route('branches.show', $branch) }}"
                                    class="hover:underline">{{ $branch->name }}</a>
                            </td>
                            <td class="px-4 py-2">{{ $branch->code }}</td>
                            <td class="px-4 py-2">{{ $branch->city }}</td>
                            <td class="px-4 py-2">
                                <flux:badge :variant="$branch->is_active ? 'success' : 'danger'">
                                    {{ $branch->is_active ? 'Active' : 'Inactive' }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-2">
                                <flux:badge :variant="$branch->is_main ? 'primary' : 'secondary'">
                                    {{ $branch->is_main ? 'Yes' : 'No' }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-2">{{ $branch->users_count }}</td>
                            <td class="px-4 py-2">{{ $branch->tickets_count }}</td>
                            <td class="px-4 py-2">{{ $branch->inventory_items_count }}</td>
                            <td class="px-4 py-2">{{ $branch->pos_sales_count }}</td>
                            <td class="px-4 py-2 flex gap-2">
                                <flux:button variant="info" :href="route('branches.edit', $branch)">Edit</flux:button>
                                <flux:button variant="danger" wire:click="delete({{ $branch->id }})">Delete
                                </flux:button>
                                <flux:button variant="secondary" wire:click="toggleStatus({{ $branch->id }})">
                                    {{ $branch->is_active ? 'Deactivate' : 'Activate' }}
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-2 text-center text-gray-500">No branches found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $branches->links() }}
        </div>
    </div>
</x-flux:modal>
