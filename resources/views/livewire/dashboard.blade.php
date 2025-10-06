<div>
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">{{ __('Dashboard') }}</flux:heading>
        <flux:text>{{ __('Overview of your repair shop operations') }}</flux:text>
    </div>

    {{-- Key Metrics Cards --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Urgent Tickets --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="mb-2 flex items-center justify-between">
                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Urgent Tickets') }}
                </flux:text>
                <flux:icon.exclamation-triangle class="size-5 text-red-500" />
            </div>
            <flux:heading size="2xl" class="mb-1">{{ $urgentTickets }}</flux:heading>
            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Requires immediate attention') }}
            </flux:text>
        </div>

        {{-- Today's Revenue --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="mb-2 flex items-center justify-between">
                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Today\'s Revenue') }}
                </flux:text>
                <flux:icon.currency-dollar class="size-5 text-green-500" />
            </div>
            <flux:heading size="2xl" class="mb-1">
                {{ __('$:amount', ['amount' => number_format($todayRevenue, 2)]) }}</flux:heading>
            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ now()->format('F j, Y') }}</flux:text>
        </div>

        {{-- Pending Invoices --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="mb-2 flex items-center justify-between">
                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Pending Invoices') }}
                </flux:text>
                <flux:icon.document-text class="size-5 text-amber-500" />
            </div>
            <flux:heading size="2xl" class="mb-1">{{ $pendingInvoices['count'] }}</flux:heading>
            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                {{ __('$:amount outstanding', ['amount' => number_format($pendingInvoices['total'], 2)]) }}</flux:text>
        </div>

        {{-- Low Stock Items --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="mb-2 flex items-center justify-between">
                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Low Stock Items') }}
                </flux:text>
                <flux:icon.cube class="size-5 text-orange-500" />
            </div>
            <flux:heading size="2xl" class="mb-1">{{ $lowStockItems }}</flux:heading>
            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Items need reordering') }}</flux:text>
        </div>
    </div>

    {{-- Tickets by Status --}}
    <div class="mb-6">
        <flux:heading size="lg" class="mb-4">{{ __('Tickets by Status') }}</flux:heading>
        <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-5">
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="mb-2 flex items-center justify-between">
                    <x-status-badge :status="\App\Enums\TicketStatus::New" />
                </div>
                <flux:heading size="xl">{{ $ticketsByStatus['new'] }}</flux:heading>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="mb-2 flex items-center justify-between">
                    <x-status-badge :status="\App\Enums\TicketStatus::InProgress" />
                </div>
                <flux:heading size="xl">{{ $ticketsByStatus['in_progress'] }}</flux:heading>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="mb-2 flex items-center justify-between">
                    <x-status-badge :status="\App\Enums\TicketStatus::WaitingForParts" />
                </div>
                <flux:heading size="xl">{{ $ticketsByStatus['waiting_for_parts'] }}</flux:heading>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="mb-2 flex items-center justify-between">
                    <x-status-badge :status="\App\Enums\TicketStatus::Completed" />
                </div>
                <flux:heading size="xl">{{ $ticketsByStatus['completed'] }}</flux:heading>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="mb-2 flex items-center justify-between">
                    <x-status-badge :status="\App\Enums\TicketStatus::Delivered" />
                </div>
                <flux:heading size="xl">{{ $ticketsByStatus['delivered'] }}</flux:heading>
            </div>
        </div>
    </div>

    {{-- Recent Tickets --}}
    <div>
        <div class="mb-4 flex items-center justify-between">
            <flux:heading size="lg">{{ __('Recent Tickets') }}</flux:heading>
            <flux:button variant="ghost" size="sm" :href="route('tickets.index')" wire:navigate>
                {{ __('View all') }}
                <flux:icon.arrow-right class="ml-1 size-4" />
            </flux:button>
        </div>

        <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
            @if ($recentTickets->isEmpty())
                <div class="p-6 text-center">
                    <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('No tickets yet') }}</flux:text>
                </div>
            @else
                <table class="w-full">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                {{ __('Ticket') }}
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                {{ __('Customer') }}
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                {{ __('Device') }}
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                {{ __('Status') }}
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                {{ __('Assigned To') }}
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                {{ __('Created') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($recentTickets as $ticket)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <a href="{{ route('tickets.show', $ticket) }}"
                                        class="font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                        wire:navigate>
                                        {{ $ticket->ticket_number }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <flux:text>{{ $ticket->customer->full_name }}</flux:text>
                                </td>
                                <td class="px-6 py-4">
                                    <flux:text class="text-sm">{{ $ticket->device->device_name }}</flux:text>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <x-status-badge :status="$ticket->status" />
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <flux:text class="text-sm">
                                        {{ $ticket->assignedTo?->name ?? __('Unassigned') }}
                                    </flux:text>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $ticket->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
