<div>
    <div class="mb-6">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('devices.index') }}" icon="device-phone-mobile">Devices
            </flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $device->device_name }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>

    <div class="mb-6 flex items-center justify-between">
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <flux:heading size="xl">{{ $device->device_name }}</flux:heading>
                @if ($device->condition)
                    <flux:badge variant="{{ $device->condition->color() }}" size="lg">
                        {{ $device->condition->label() }}
                    </flux:badge>
                @endif
            </div>
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
        {{-- Device Statistics --}}
        <div class="lg:col-span-3">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Repairs</flux:text>
                    <div class="mt-2 flex items-baseline gap-2">
                        <flux:heading size="2xl">{{ $device->repair_count }}</flux:heading>
                        <flux:text class="text-sm text-zinc-500">tickets</flux:text>
                    </div>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Repair Cost
                    </flux:text>
                    <div class="mt-2 flex items-baseline gap-2">
                        <flux:heading size="2xl">GH₵{{ number_format($device->total_repair_cost, 2) }}
                        </flux:heading>
                    </div>
                </div>

                @if ($device->warranty_status)
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Warranty Status
                        </flux:text>
                        <div class="mt-2">
                            @if ($device->isUnderWarranty())
                                <flux:badge variant="success" size="lg">{{ $device->warranty_status }}</flux:badge>
                            @else
                                <flux:badge variant="danger" size="lg">{{ $device->warranty_status }}</flux:badge>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Registered</flux:text>
                    <div class="mt-2">
                        <flux:text class="text-sm">{{ $device->created_at->format('M d, Y') }}</flux:text>
                        <flux:text class="text-xs text-zinc-500">{{ $device->created_at->diffForHumans() }}</flux:text>
                    </div>
                </div>
            </div>
        </div>

        {{-- Device Information --}}
        <div class="lg:col-span-2">
            <div class="space-y-6">
                {{-- Basic Device Details --}}
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

                        @if ($device->color)
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Color
                                </flux:text>
                                <flux:text class="mt-1">{{ $device->color }}</flux:text>
                            </div>
                        @endif

                        @if ($device->storage_capacity)
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Storage
                                </flux:text>
                                <flux:text class="mt-1">{{ $device->storage_capacity }}</flux:text>
                            </div>
                        @endif

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
                    </div>
                </div>

                {{-- Condition Assessment --}}
                @if ($device->condition || $device->condition_notes)
                    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:heading size="lg" class="mb-4">Condition Assessment</flux:heading>

                        @if ($device->condition)
                            <div class="mb-4">
                                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Current
                                    Condition</flux:text>
                                <div class="mt-2">
                                    <flux:badge variant="{{ $device->condition->color() }}" size="lg">
                                        {{ $device->condition->label() }}
                                    </flux:badge>
                                </div>
                            </div>
                        @endif

                        @if ($device->condition_notes)
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Condition
                                    Notes</flux:text>
                                <flux:text class="mt-1">{{ $device->condition_notes }}</flux:text>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Warranty Information --}}
                @if ($device->purchase_date || $device->warranty_expiry || $device->warranty_provider)
                    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:heading size="lg" class="mb-4">Warranty Information</flux:heading>

                        <div class="grid gap-4 sm:grid-cols-2">
                            @if ($device->purchase_date)
                                <div>
                                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Purchase
                                        Date</flux:text>
                                    <flux:text class="mt-1">{{ $device->purchase_date->format('M d, Y') }}
                                    </flux:text>
                                </div>
                            @endif

                            @if ($device->warranty_expiry)
                                <div>
                                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Warranty
                                        Expiry</flux:text>
                                    <flux:text class="mt-1">{{ $device->warranty_expiry->format('M d, Y') }}
                                    </flux:text>
                                </div>
                            @endif

                            @if ($device->warranty_provider)
                                <div class="sm:col-span-2">
                                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Warranty
                                        Provider</flux:text>
                                    <flux:text class="mt-1">{{ $device->warranty_provider }}</flux:text>
                                </div>
                            @endif

                            @if ($device->warranty_notes)
                                <div class="sm:col-span-2">
                                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Warranty
                                        Notes</flux:text>
                                    <flux:text class="mt-1">{{ $device->warranty_notes }}</flux:text>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Additional Notes --}}
                @if ($device->notes)
                    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:heading size="lg" class="mb-4">Additional Notes</flux:heading>
                        <flux:text>{{ $device->notes }}</flux:text>
                    </div>
                @endif

                {{-- Device Photos --}}
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <livewire:devices.manage-photos :device="$device" />
                </div>

                {{-- Repair History --}}
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
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
                                            <flux:text class="mt-1 text-sm">{{ $ticket->problem_description }}
                                            </flux:text>
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
