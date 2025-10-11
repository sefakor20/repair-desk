<div>
    <div class="mb-6">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('devices.index') }}" icon="device-phone-mobile">Devices
            </flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $device->device_name }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $device->device_name }}</flux:heading>
            <flux:text class="mt-1">Device details and repair history</flux:text>
        </div>
        <div class="flex gap-2">
            @can('update', $device)
                <flux:button href="{{ route('devices.edit', $device) }}" icon="pencil" variant="primary">
                    Edit Device
                </flux:button>
            @endcan
            @can('delete', $device)
                <flux:button wire:click="delete" wire:confirm="Are you sure you want to delete this device?" icon="trash"
                    variant="danger">
                    Delete
                </flux:button>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <flux:callout variant="success" class="mb-6">
            {{ session('success') }}
        </flux:callout>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Device Information --}}
        <div class="lg:col-span-2">
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">Device Information</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Brand</flux:text>
                        <flux:text class="mt-1">{{ $device->brand }}</flux:text>
                    </div>

                    <div>
                        <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Model</flux:text>
                        <flux:text class="mt-1">{{ $device->model }}</flux:text>
                    </div>

                    <div>
                        <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Type</flux:text>
                        <div class="mt-1">
                            <flux:badge>{{ $device->type }}</flux:badge>
                        </div>
                    </div>

                    <div>
                        <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Serial Number
                        </flux:text>
                        <flux:text class="mt-1">{{ $device->serial_number ?: '—' }}</flux:text>
                    </div>

                    @if ($device->imei)
                        <div>
                            <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">IMEI</flux:text>
                            <flux:text class="mt-1">{{ $device->imei }}</flux:text>
                        </div>
                    @endif

                    @if ($device->notes)
                        <div class="sm:col-span-2">
                            <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Notes</flux:text>
                            <flux:text class="mt-1">{{ $device->notes }}</flux:text>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Repair History --}}
            <div class="mt-6 rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">Repair History</flux:heading>

                @if ($device->tickets->isEmpty())
                    <div class="py-8 text-center">
                        <flux:icon.wrench-screwdriver class="mx-auto size-12 text-zinc-400" />
                        <flux:text class="mt-2 text-zinc-500">No repair tickets yet</flux:text>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($device->tickets as $ticket)
                            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <a href="{{ route('tickets.show', $ticket) }}"
                                            class="font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                            Ticket #{{ $ticket->ticket_number }}
                                        </a>
                                        <flux:text class="mt-1 text-sm">{{ $ticket->problem_description }}</flux:text>
                                        <div
                                            class="mt-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                                            <span>Created {{ $ticket->created_at->diffForHumans() }}</span>
                                            @if ($ticket->createdBy)
                                                <span>•</span>
                                                <span>by {{ $ticket->createdBy->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <flux:badge
                                        variant="{{ $ticket->status->value === 'completed' ? 'success' : ($ticket->status->value === 'cancelled' ? 'danger' : 'primary') }}">
                                        {{ ucfirst($ticket->status->value) }}
                                    </flux:badge>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Customer Information --}}
        <div>
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">Customer</flux:heading>

                <div class="space-y-4">
                    <div>
                        <a href="{{ route('customers.show', $device->customer) }}"
                            class="text-lg font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            {{ $device->customer->full_name }}
                        </a>
                    </div>

                    <div>
                        <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Email</flux:text>
                        <flux:text class="mt-1">{{ $device->customer->email }}</flux:text>
                    </div>

                    <div>
                        <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Phone</flux:text>
                        <flux:text class="mt-1">{{ $device->customer->phone }}</flux:text>
                    </div>

                    @if ($device->customer->address)
                        <div>
                            <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Address</flux:text>
                            <flux:text class="mt-1">{{ $device->customer->address }}</flux:text>
                        </div>
                    @endif

                    <div class="pt-2">
                        <flux:button href="{{ route('customers.show', $device->customer) }}" variant="ghost"
                            icon="arrow-right" class="w-full">
                            View Customer
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
