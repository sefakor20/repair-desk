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
        <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
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
                                Serial Number
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
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $device->serial_number ?: '—' }}
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
    @endif
</div>
