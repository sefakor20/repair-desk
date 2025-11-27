<div class="space-y-6 relative">
    {{-- Loading Overlay --}}
    <x-loading-overlay wire:loading
        wire:target="search, statusFilter, priorityFilter, assignedFilter, clearFilters, delete" />
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Tickets</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Manage repair tickets and track progress</p>
        </div>

        @if (auth()->check() &&
                auth()->user()->hasAnyStaffPermission(['manage_tickets', 'create_tickets']) &&
                auth()->user()->can('create', App\Models\Ticket::class))
            <a href="{{ route('tickets.create') }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 dark:focus:ring-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Ticket
            </a>
        @endif
    </div>

    @if (session('success'))
        <div
            class="animate-in fade-in slide-in-from-top-2 duration-300 rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <!-- Search -->
        <div class="sm:col-span-2">
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg wire:loading.remove wire:target="search" class="h-5 w-5 text-zinc-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <svg wire:loading wire:target="search" class="h-5 w-5 animate-spin text-zinc-400" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search tickets, customers..."
                    class="block w-full rounded-lg border border-zinc-200 bg-white py-2.5 pl-10 pr-3 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500 dark:focus:border-white dark:focus:ring-white">
            </div>
        </div>

        <!-- Branch Filter -->
        <div>
            <select wire:model.live="branchFilter"
                class="block w-full rounded-lg border border-zinc-200 bg-white py-2.5 px-3 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-white dark:focus:ring-white">
                <option value="">All Branches</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Status Filter -->
        <div>
            <select wire:model.live="statusFilter"
                class="block w-full rounded-lg border border-zinc-200 bg-white py-2.5 px-3 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-white dark:focus:ring-white">
                <option value="">All Statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>

        <!-- Priority Filter -->
        <div>
            <select wire:model.live="priorityFilter"
                class="block w-full rounded-lg border border-zinc-200 bg-white py-2.5 px-3 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-white dark:focus:ring-white">
                <option value="">All Priorities</option>
                @foreach ($priorities as $priority)
                    <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Assigned To Filter & Clear Filters -->
    <div class="flex items-center gap-4">
        <div class="flex-1">
            <select wire:model.live="assignedFilter"
                class="block w-full rounded-lg border border-zinc-200 bg-white py-2.5 px-3 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-white dark:focus:ring-white">
                <option value="">All Technicians</option>
                <option value="unassigned">Unassigned</option>
                @foreach ($technicians as $technician)
                    <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                @endforeach
            </select>
        </div>

        @if ($search || $statusFilter || $priorityFilter || $assignedFilter || $branchFilter)
            <button wire:click="clearFilters"
                class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition-all hover:bg-zinc-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Clear Filters
            </button>
        @endif
    </div>

    <!-- Desktop Table View (hidden on mobile) -->
    <div
        class="hidden overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800 lg:block">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-900">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Ticket
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Customer
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Device
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Status
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Assigned To
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                    @forelse ($tickets as $ticket)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div>
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $ticket->ticket_number }}
                                    </div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ Str::limit($ticket->problem_description, 40) }}
                                    </div>
                                    <div class="mt-1 flex items-center gap-2">
                                        <x-status-badge :status="$ticket->status" />
                                        <x-status-badge :status="$ticket->priority" />
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-zinc-900 dark:text-white">{{ $ticket->customer->full_name }}
                                </div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $ticket->customer->email }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $ticket->device->device_name }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $ticket->created_at->diffForHumans() }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                @if ($ticket->assignedTo)
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-200 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                                            {{ strtoupper(substr($ticket->assignedTo->name, 0, 2)) }}
                                        </div>
                                        <span>{{ $ticket->assignedTo->name }}</span>
                                    </div>
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-500">Unassigned</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @can('view', $ticket)
                                        <a href="{{ route('tickets.show', $ticket) }}" wire:navigate
                                            class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white"
                                            title="View">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                    @endcan

                                    @can('update', $ticket)
                                        <a href="{{ route('tickets.edit', $ticket) }}" wire:navigate
                                            class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white"
                                            title="Edit">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endcan

                                    @can('delete', $ticket)
                                        <button wire:click="delete('{{ $ticket->id }}')"
                                            wire:confirm="Are you sure you want to delete this ticket?"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            title="Delete">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                @if ($search || $statusFilter || $priorityFilter || $assignedFilter)
                                    <x-empty-state icon="search" title="{{ __('No tickets found') }}"
                                        description="{{ __('Try adjusting your search or filters') }}" />
                                @else
                                    <x-empty-state icon="document" title="{{ __('No tickets found') }}"
                                        description="{{ __('Get started by creating a new ticket') }}"
                                        :action-route="route('tickets.create')" action-label="{{ __('New Ticket') }}" />
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tickets->hasPages())
            <div class="border-t border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>

    <!-- Mobile Card View (visible on mobile) -->
    <div class="space-y-4 lg:hidden">
        @forelse ($tickets as $ticket)
            <div
                class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm transition-shadow hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800">
                <!-- Ticket Header -->
                <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ $ticket->ticket_number }}
                            </div>
                            <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ Str::limit($ticket->problem_description, 60) }}
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <x-status-badge :status="$ticket->status" />
                                <x-status-badge :status="$ticket->priority" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ticket Details -->
                <div class="px-4 py-3">
                    <dl class="space-y-2.5">
                        <div class="flex items-center justify-between text-sm">
                            <dt class="font-medium text-zinc-500 dark:text-zinc-400">Customer</dt>
                            <dd class="text-right text-zinc-900 dark:text-white">
                                <div>{{ $ticket->customer->full_name }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $ticket->customer->email }}
                                </div>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <dt class="font-medium text-zinc-500 dark:text-zinc-400">Device</dt>
                            <dd class="text-zinc-900 dark:text-white">{{ $ticket->device->device_name }}</dd>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <dt class="font-medium text-zinc-500 dark:text-zinc-400">Assigned To</dt>
                            <dd class="text-zinc-900 dark:text-white">
                                @if ($ticket->assignedTo)
                                    <div class="flex items-center justify-end gap-2">
                                        <span>{{ $ticket->assignedTo->name }}</span>
                                        <div
                                            class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-200 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                                            {{ strtoupper(substr($ticket->assignedTo->name, 0, 2)) }}
                                        </div>
                                    </div>
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-500">Unassigned</span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <dt class="font-medium text-zinc-500 dark:text-zinc-400">Created</dt>
                            <dd class="text-zinc-900 dark:text-white">{{ $ticket->created_at->diffForHumans() }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Actions -->
                <div class="border-t border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-end gap-3">
                        @can('view', $ticket)
                            <a href="{{ route('tickets.show', $ticket) }}" wire:navigate
                                class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                View
                            </a>
                        @endcan

                        @can('update', $ticket)
                            <a href="{{ route('tickets.edit', $ticket) }}" wire:navigate
                                class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </a>
                        @endcan

                        @can('delete', $ticket)
                            <button wire:click="delete('{{ $ticket->id }}')"
                                wire:confirm="Are you sure you want to delete this ticket?"
                                class="inline-flex items-center gap-2 rounded-lg border border-red-300 bg-white px-3 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-600 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20">
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
        @empty
            @if ($search || $statusFilter || $priorityFilter || $assignedFilter)
                <x-empty-state icon="search" title="{{ __('No tickets found') }}"
                    description="{{ __('Try adjusting your search or filters') }}" />
            @else
                <x-empty-state icon="document" title="{{ __('No tickets found') }}"
                    description="{{ __('Get started by creating a new ticket') }}" :action-route="route('tickets.create')"
                    action-label="{{ __('New Ticket') }}" />
            @endif
        @endforelse

        @if ($tickets->hasPages())
            <div class="rounded-lg border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
