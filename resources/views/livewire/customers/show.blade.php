<div class="space-y-6">
    <!-- Header -->
    <div>
        <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
            <a href="{{ route('customers.index') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-white">
                Customers
            </a>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span>{{ $customer->full_name }}</span>
        </div>

        <div class="mt-2 flex items-start justify-between">
            <div class="flex items-center gap-4">
                <div
                    class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-200 text-xl font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                    {{ strtoupper(substr($customer->first_name, 0, 1) . substr($customer->last_name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $customer->full_name }}</h1>
                    @if ($customer->tags)
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach ($customer->tags as $tag)
                                <span
                                    class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2">
                @can('update', $customer)
                    <a href="{{ route('customers.edit', $customer) }}" wire:navigate
                        class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-900 hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </a>
                @endcan

                @can('delete', $customer)
                    <button wire:click="deleteCustomer"
                        wire:confirm="Are you sure you want to delete this customer? This action cannot be undone."
                        class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete
                    </button>
                @endcan
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Contact Information -->
        <div class="lg:col-span-1">
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Contact Information</h2>

                <dl class="mt-6 space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Email</dt>
                        <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                            <a href="mailto:{{ $customer->email }}" class="hover:underline">{{ $customer->email }}</a>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Phone</dt>
                        <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                            <a href="tel:{{ $customer->phone }}" class="hover:underline">{{ $customer->phone }}</a>
                        </dd>
                    </div>

                    @if ($customer->address)
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Address</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $customer->address }}</dd>
                        </div>
                    @endif

                    @if ($customer->notes)
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Notes</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $customer->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Stats -->
            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $customer->devices->count() }}
                    </div>
                    <div class="text-sm text-zinc-600 dark:text-zinc-400">Devices</div>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $customer->tickets->count() }}
                    </div>
                    <div class="text-sm text-zinc-600 dark:text-zinc-400">Tickets</div>
                </div>
            </div>
        </div>

        <!-- Devices & Tickets -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Devices -->
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                <div class="border-b border-zinc-200 p-6 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Devices</h2>
                </div>

                <div class="p-6">
                    @forelse ($customer->devices as $device)
                        <div class="mb-4 last:mb-0 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="font-medium text-zinc-900 dark:text-white">{{ $device->device_name }}
                                    </h3>
                                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                        <span class="font-medium">Type:</span> {{ $device->type }}
                                    </p>
                                    @if ($device->serial_number)
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                            <span class="font-medium">S/N:</span> {{ $device->serial_number }}
                                        </p>
                                    @endif
                                    @if ($device->imei)
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                            <span class="font-medium">IMEI:</span> {{ $device->imei }}
                                        </p>
                                    @endif
                                </div>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $device->tickets->count() }}
                                    {{ Str::plural('ticket', $device->tickets->count()) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">No devices registered yet</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Tickets -->
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                <div class="border-b border-zinc-200 p-6 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Recent Tickets</h2>
                </div>

                <div class="p-6">
                    @forelse ($customer->tickets->take(5) as $ticket)
                        <div
                            class="mb-4 last:mb-0 flex items-center justify-between rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <div>
                                <h3 class="font-medium text-zinc-900 dark:text-white">{{ $ticket->ticket_number }}</h3>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $ticket->problem_description }}</p>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                        style="background-color: {{ $ticket->status->color() }}20; color: {{ $ticket->status->color() }}">
                                        {{ $ticket->status->label() }}
                                    </span>
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                        style="background-color: {{ $ticket->priority->color() }}20; color: {{ $ticket->priority->color() }}">
                                        {{ $ticket->priority->label() }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $ticket->created_at->diffForHumans() }}
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">No tickets yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
