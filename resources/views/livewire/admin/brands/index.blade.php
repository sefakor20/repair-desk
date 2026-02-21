<div>
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <flux:heading size="lg">Device Brands</flux:heading>
                <flux:text variant="muted">Manage device brands for phones, laptops, and other devices</flux:text>
            </div>
            @can('create', App\Models\DeviceBrand::class)
                <flux:button :href="route('admin.brands.create')" variant="primary" icon="plus">
                    Add Brand
                </flux:button>
            @endcan
        </div>
    </div>

    <div class="space-y-6">
        <!-- Search and Filters -->
        <div class="flex gap-4">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search brands..."
                class="flex-1" icon="magnifying-glass" />

            <flux:select wire:model.live="categoryFilter" placeholder="All Categories" class="w-64">
                <option value="">All Categories</option>
                @foreach ($categories as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </flux:select>
        </div>

        <!-- Brands Table -->
        @if ($this->brands->count())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Brand Name
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Category
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Models
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
                        @foreach ($this->brands as $brand)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $brand->name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <flux:badge color="blue">{{ $brand->category->label() }}</flux:badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $brand->models->count() }} model(s)
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($brand->is_active)
                                        <flux:badge color="green">Active</flux:badge>
                                    @else
                                        <flux:badge color="red">Inactive</flux:badge>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        @can('update', $brand)
                                            <flux:button wire:click="toggleStatus({{ $brand->id }})"
                                                variant="ghost" size="sm">
                                                {{ $brand->is_active ? 'Deactivate' : 'Activate' }}
                                            </flux:button>
                                        @endcan

                                        @can('update', $brand)
                                            <flux:button :href="route('admin.brands.edit', $brand)" variant="subtle"
                                                size="sm" icon="pencil">
                                                Edit
                                            </flux:button>
                                        @endcan

                                        @can('delete', $brand)
                                            <flux:button wire:click="delete({{ $brand->id }})"
                                                wire:confirm="Are you sure you want to delete this brand? This will also delete all associated models."
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
                {{ $this->brands->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No brands found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if ($search || $categoryFilter)
                        Try adjusting your search or filter criteria.
                    @else
                        Get started by creating a new brand.
                    @endif
                </p>
                @can('create', App\Models\DeviceBrand::class)
                    <div class="mt-6">
                        <flux:button :href="route('admin.brands.create')" variant="primary" icon="plus">
                            Add Brand
                        </flux:button>
                    </div>
                @endcan
            </div>
        @endif
    </div>
</div>
