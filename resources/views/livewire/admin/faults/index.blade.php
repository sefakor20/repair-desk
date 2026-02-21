<div>
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <flux:heading size="lg">Common Faults</flux:heading>
                <flux:text variant="muted">Manage pre-defined device faults for quick selection during repairs
                </flux:text>
            </div>
            @can('create', App\Models\CommonFault::class)
                <flux:button :href="route('admin.faults.create')" variant="primary" icon="plus">
                    Add Fault
                </flux:button>
            @endcan
        </div>
    </div>

    <div class="space-y-6">
        <!-- Search and Filters -->
        <div class="flex gap-4">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search faults..."
                class="flex-1" icon="magnifying-glass" />

            <flux:select wire:model.live="categoryFilter" placeholder="All Categories" class="w-64">
                <option value="">All Categories</option>
                @foreach ($categories as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </flux:select>
        </div>

        <!-- Faults Table -->
        @if ($this->faults->count())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Fault Name
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Category
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($this->faults as $fault)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $fault->name }}
                                            </div>
                                            @if ($fault->description)
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ Str::limit($fault->description, 80) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($fault->device_category)
                                        <flux:badge color="blue">{{ $fault->device_category->label() }}
                                        </flux:badge>
                                    @else
                                        <flux:badge color="zinc">Universal</flux:badge>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($fault->is_active)
                                        <flux:badge color="green">Active</flux:badge>
                                    @else
                                        <flux:badge color="red">Inactive</flux:badge>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        @can('update', $fault)
                                            <flux:button wire:click="toggleStatus({{ $fault->id }})"
                                                variant="ghost" size="sm">
                                                {{ $fault->is_active ? 'Deactivate' : 'Activate' }}
                                            </flux:button>
                                        @endcan

                                        @can('update', $fault)
                                            <flux:button :href="route('admin.faults.edit', $fault)" variant="subtle"
                                                size="sm" icon="pencil">
                                                Edit
                                            </flux:button>
                                        @endcan

                                        @can('delete', $fault)
                                            <flux:button wire:click="delete({{ $fault->id }})"
                                                wire:confirm="Are you sure you want to delete this fault?"
                                                variant="danger" size="sm" icon="trash">
                                                Delete
                                            </flux:button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $this->faults->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No faults found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if ($search || $categoryFilter)
                        Try adjusting your search or filter criteria.
                    @else
                        Get started by creating a new fault type.
                    @endif
                </p>
                @can('create', App\Models\CommonFault::class)
                    <div class="mt-6">
                        <flux:button :href="route('admin.faults.create')" variant="primary" icon="plus">
                            Add Fault
                        </flux:button>
                    </div>
                @endcan
            </div>
        @endif
    </div>
</div>
