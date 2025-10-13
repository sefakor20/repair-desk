<div>
    <div class="mb-6 flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search tickets..." type="search" />
        </div>

        <div class="w-full sm:w-48">
            <flux:select wire:model.live="filterStatus">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="on_hold">On Hold</option>
                <option value="cancelled">Cancelled</option>
            </flux:select>
        </div>
    </div>

    @if ($tickets->count() > 0)
        <div class="space-y-4">
            @foreach ($tickets as $ticket)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-md transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Ticket #{{ $ticket->ticket_number }}
                                </h3>
                                <flux:badge
                                    :variant="match($ticket->status->value) {
                                                                                                            'pending' => 'warning',
                                                                                                            'in_progress' => 'info',
                                                                                                            'completed' => 'success',
                                                                                                            'cancelled' => 'danger',
                                                                                                            default => 'secondary'
                                                                                                        }">
                                    {{ str($ticket->status->value)->replace('_', ' ')->title() }}
                                </flux:badge>
                            </div>

                            <p class="text-gray-600 dark:text-gray-400 mb-3">{{ $ticket->description }}</p>

                            <div class="flex flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400">
                                @if ($ticket->device)
                                    <div class="flex items-center gap-1">
                                        <flux:icon.device-phone-mobile class="w-4 h-4" />
                                        <span>{{ $ticket->device->brand }} {{ $ticket->device->model }}</span>
                                    </div>
                                @endif

                                @if ($ticket->assignedTo)
                                    <div class="flex items-center gap-1">
                                        <flux:icon.user class="w-4 h-4" />
                                        <span>Assigned to {{ $ticket->assignedTo->name }}</span>
                                    </div>
                                @endif

                                <div class="flex items-center gap-1">
                                    <flux:icon.calendar class="w-4 h-4" />
                                    <span>{{ $ticket->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <flux:button
                            href="{{ route('portal.tickets.show', ['customer' => $customer->id, 'token' => $customer->portal_access_token, 'ticket' => $ticket->id]) }}"
                            variant="ghost" size="sm">
                            View Details
                        </flux:button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $tickets->links() }}
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
            <flux:icon.inbox class="w-16 h-16 mx-auto text-gray-400 mb-4" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No tickets found</h3>
            <p class="text-gray-600 dark:text-gray-400">
                @if ($search || $filterStatus !== 'all')
                    Try adjusting your filters to find what you're looking for.
                @else
                    You don't have any repair tickets yet.
                @endif
            </p>
        </div>
    @endif
</div>
