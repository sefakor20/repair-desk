<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">Devices</flux:heading>
            <flux:text class="mt-1">Manage registered customer devices and their repair history.</flux:text>
        </div>
        @can('create', App\Models\Device::class)
            <flux:button href="{{ route('devices.create') }}" icon="plus" variant="primary">
                Register Device
            </flux:button>
        @endcan
    </div>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search devices..." class="flex-1"
            icon="magnifying-glass" />

        <flux:select wire:model.live="customerFilter" placeholder="All Customers" class="sm:w-64">
            <option value="">All Customers</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->full_name }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="typeFilter" placeholder="All Types" class="sm:w-48">
            <option value="">All Types</option>
            @foreach ($types as $type)
                <option value="{{ $type }}">{{ $type }}</option>
            @endforeach
        </flux:select>

        @if ($search || $customerFilter || $typeFilter)
            <flux:button wire:click="clearFilters" variant="ghost" icon="x-mark">
                Clear
            </flux:button>
        @endif
    </div>

    @if (session('success'))
        <flux:callout variant="success" class="mb-6">
            {{ session('success') }}
        </flux:callout>
    @endif

    @if ($devices->isEmpty())
        <div class="rounded-lg border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-800">
            <flux:icon.device-phone-mobile class="mx-auto size-12 text-zinc-400" />
            <flux:heading size="lg" class="mt-4">
                @if ($search || $customerFilter || $typeFilter)
                    No devices found
                @else
                    No devices registered yet
                @endif
            </flux:heading>
            <flux:text class="mt-2">
                @if ($search || $customerFilter || $typeFilter)
                    Try adjusting your search or filters
                @else
                    Register your first device to start tracking repairs
                @endif
            </flux:text>
            @if (!$search && !$customerFilter && !$typeFilter)
                @can('create', App\Models\Device::class)
                    <flux:button href="{{ route('devices.create') }}" variant="primary" class="mt-4">
                        Register Device
                    </flux:button>
                @endcan
            @endif
        </div>
    @else
        <!-- Desktop Table View (hidden on mobile) -->
        <div
            class="hidden overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800 lg:block">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                Device
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                Customer
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                Type
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                Condition
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                Warranty
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                Tickets
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($devices as $device)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <a href="{{ route('devices.show', $device) }}"
                                        class="font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                        {{ $device->device_name }}
                                    </a>
                                    @if ($device->storage_capacity)
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $device->storage_capacity }}
                                        </div>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <a href="{{ route('customers.show', $device->customer) }}"
                                        class="text-zinc-900 hover:text-blue-600 dark:text-zinc-100 dark:hover:text-blue-400">
                                        {{ $device->customer->full_name }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <flux:badge>{{ $device->type }}</flux:badge>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if ($device->condition)
                                        <flux:badge variant="{{ $device->condition->color() }}">
                                            {{ $device->condition->label() }}
                                        </flux:badge>
                                    @else
                                        <span class="text-sm text-zinc-400">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if ($device->warranty_status)
                                        @if ($device->isUnderWarranty())
                                            <flux:badge variant="success" size="sm">Active</flux:badge>
                                        @else
                                            <flux:badge variant="danger" size="sm">Expired</flux:badge>
                                        @endif
                                    @else
                                        <span class="text-sm text-zinc-400">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $device->tickets_count }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <div class="flex items-center justify-end gap-2">
                                        <flux:button href="{{ route('devices.show', $device) }}" variant="ghost"
                                            size="sm" icon="eye">
                                            View
                                        </flux:button>
                                        @can('update', $device)
                                            <flux:button href="{{ route('devices.edit', $device) }}" variant="ghost"
                                                size="sm" icon="pencil">
                                                Edit
                                            </flux:button>
                                        @endcan
                                        @can('delete', $device)
                                            <flux:button wire:click="delete('{{ $device->id }}')"
                                                wire:confirm="Are you sure you want to delete this device?" variant="ghost"
                                                size="sm" icon="trash" class="text-red-600 hover:text-red-700">
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
        </div>

        <div class="mt-6">
            {{ $devices->links() }}
        </div>

        <!-- Mobile Card View (visible on mobile) -->
        <div class="space-y-4 lg:hidden">
            @foreach ($devices as $device)
                <div
                    class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm transition-shadow hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800">
                    <!-- Device Header -->
                    <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <a href="{{ route('devices.show', $device) }}"
                                    class="text-sm font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ $device->device_name }}
                                </a>
                                <div class="mt-1">
                                    <flux:badge>{{ $device->type }}</flux:badge>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Device Details -->
                    <div class="px-4 py-3">
                        <dl class="space-y-2.5">
                            <div class="flex items-center justify-between text-sm">
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Customer</dt>
                                <dd class="text-zinc-900 dark:text-white">
                                    <a href="{{ route('customers.show', $device->customer) }}"
                                        class="hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ $device->customer->full_name }}
                                    </a>
                                </dd>
                            </div>
                            @if ($device->storage_capacity)
                                <div class="flex items-center justify-between text-sm">
                                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">Storage</dt>
                                    <dd class="text-zinc-900 dark:text-white">{{ $device->storage_capacity }}</dd>
                                </div>
                            @endif
                            @if ($device->condition)
                                <div class="flex items-center justify-between text-sm">
                                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">Condition</dt>
                                    <dd>
                                        <flux:badge variant="{{ $device->condition->color() }}">
                                            {{ $device->condition->label() }}
                                        </flux:badge>
                                    </dd>
                                </div>
                            @endif
                            @if ($device->warranty_status)
                                <div class="flex items-center justify-between text-sm">
                                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">Warranty</dt>
                                    <dd>
                                        @if ($device->isUnderWarranty())
                                            <flux:badge variant="success" size="sm">Active</flux:badge>
                                        @else
                                            <flux:badge variant="danger" size="sm">Expired</flux:badge>
                                        @endif
                                    </dd>
                                </div>
                            @endif
                            <div class="flex items-center justify-between text-sm">
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Tickets</dt>
                                <dd class="text-zinc-900 dark:text-white">{{ $device->tickets_count }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Actions -->
                    <div class="border-t border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-center justify-end gap-3">
                            <flux:button href="{{ route('devices.show', $device) }}" variant="ghost" size="sm"
                                icon="eye">
                                View
                            </flux:button>
                            @can('update', $device)
                                <flux:button href="{{ route('devices.edit', $device) }}" variant="ghost" size="sm"
                                    icon="pencil">
                                    Edit
                                </flux:button>
                            @endcan
                            @can('delete', $device)
                                <flux:button wire:click="delete('{{ $device->id }}')"
                                    wire:confirm="Are you sure you want to delete this device?" variant="ghost"
                                    size="sm" icon="trash" class="text-red-600 hover:text-red-700">
                                    Delete
                                </flux:button>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="mt-6">
                {{ $devices->links() }}
            </div>
        </div>
    @endif
</div>
