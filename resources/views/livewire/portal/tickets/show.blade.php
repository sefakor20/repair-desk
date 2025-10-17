<div>
    <x-layouts.portal-content :customer="$customer" :title="'Ticket #' . $ticket->ticket_number">
        <div class="space-y-6">
            {{-- Back Button --}}
            <div>
                <flux:button
                    href="{{ route('portal.tickets.index', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}"
                    variant="ghost" size="sm">
                    <flux:icon.arrow-left class="w-4 h-4" />
                    Back to Tickets
                </flux:button>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                            Ticket #{{ $ticket->ticket_number }}
                        </h1>
                        <flux:badge
                            :variant="match($ticket->status->value) {
                                                                                                                                                                                    'pending' => 'warning',
                                                                                                                                                                                    'in_progress' => 'info',
                                                                                                                                                                                    'completed' => 'success',
                                                                                                                                                                                    'cancelled' => 'danger',
                                                                                                                                                                                    default => 'secondary'
                                                                                                                                                                                }"
                            size="lg">
                            {{ str($ticket->status->value)->replace('_', ' ')->title() }}
                        </flux:badge>
                    </div>

                    <div class="text-right text-sm text-gray-500 dark:text-gray-400">
                        <div>Created {{ $ticket->created_at->format('M d, Y') }}</div>
                        @if ($ticket->completed_at)
                            <div>Completed {{ $ticket->completed_at->format('M d, Y') }}</div>
                        @endif
                    </div>
                </div>

                <flux:separator class="my-6" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Device
                            Information
                        </h3>
                        @if ($ticket->device)
                            <p class="text-gray-900 dark:text-white">{{ $ticket->device->brand }}
                                {{ $ticket->device->model }}
                            </p>
                            @if ($ticket->device->serial_number)
                                <p class="text-sm text-gray-600 dark:text-gray-400">Serial:
                                    {{ $ticket->device->serial_number }}
                                </p>
                            @endif
                        @else
                            <p class="text-gray-600 dark:text-gray-400">No device specified</p>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Assigned
                            Technician
                        </h3>
                        @if ($ticket->assignedTo)
                            <p class="text-gray-900 dark:text-white">{{ $ticket->assignedTo->name }}</p>
                        @else
                            <p class="text-gray-600 dark:text-gray-400">Not assigned yet</p>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Priority</h3>
                        <flux:badge
                            :variant="match($ticket->priority->value) {
                                                                                                                                                                                    'high' => 'danger',
                                                                                                                                                                                    'medium' => 'warning',
                                                                                                                                                                                    'low' => 'secondary',
                                                                                                                                                                                    default => 'secondary'
                                                                                                                                                                                }">
                            {{ str($ticket->priority->value)->title() }}
                        </flux:badge>
                    </div>

                    @if ($ticket->estimated_completion_date)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Estimated
                                Completion</h3>
                            <p class="text-gray-900 dark:text-white">
                                {{ $ticket->estimated_completion_date->format('M d, Y') }}
                            </p>
                        </div>
                    @endif
                </div>

                <flux:separator class="my-6" />

                <div>
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Description</h3>
                    <p class="text-gray-900 dark:text-white">{{ $ticket->description }}</p>
                </div>
            </div>

            @if ($ticket->parts->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Parts Used</h2>
                    <div class="space-y-2">
                        @foreach ($ticket->parts as $part)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-900 dark:text-white">{{ $part->name }}</span>
                                <span class="text-gray-600 dark:text-gray-400">Qty: {{ $part->pivot->quantity }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($ticket->notes->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Updates & Notes</h2>
                    <div class="space-y-4">
                        @foreach ($ticket->notes as $note)
                            <div class="border-l-4 border-blue-500 pl-4">
                                <div class="flex items-center justify-between mb-1">
                                    <span
                                        class="text-sm font-medium text-gray-900 dark:text-white">{{ $note->user->name }}</span>
                                    <span
                                        class="text-xs text-gray-500 dark:text-gray-400">{{ $note->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-gray-700 dark:text-gray-300">{{ $note->content }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($ticket->invoice)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Invoice</h2>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Invoice Number:</span>
                            <span
                                class="ml-2 text-gray-900 dark:text-white font-medium">{{ $ticket->invoice->invoice_number }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Total Amount:</span>
                            <span class="ml-2 text-gray-900 dark:text-white font-medium">GH₵
                                {{ number_format($ticket->invoice->total, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Status:</span>
                            <flux:badge
                                :variant="$ticket->invoice->status->value === 'paid' ? 'success' : 'warning'"
                                class="ml-2">
                                {{ str($ticket->invoice->status->value)->title() }}
                            </flux:badge>
                        </div>
                        @if ($ticket->invoice->payments->count() > 0)
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Paid Amount:</span>
                                <span class="ml-2 text-gray-900 dark:text-white font-medium">GH₵
                                    {{ number_format($ticket->invoice->payments->sum('amount'), 2) }}</span>
                            </div>
                        @endif
                    </div>

                    @if (in_array($ticket->invoice->status->value, ['pending', 'overdue']) && $ticket->invoice->balance_due > 0)
                        <div class="mt-4">
                            <flux:button
                                href="{{ route('portal.invoices.pay', ['customer' => $customer->id, 'token' => $customer->portal_access_token, 'invoice' => $ticket->invoice->id]) }}"
                                variant="primary">
                                <flux:icon.credit-card class="w-4 h-4" />
                                Pay Now - GH₵ {{ number_format($ticket->invoice->balance_due, 2) }}
                            </flux:button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </x-layouts.portal-content>
</div>
