<div>
    <x-layouts.portal-content :customer="$customer" :title="$device->brand . ' ' . $device->model">
        <div class="space-y-6">
            {{-- Back Button --}}
            <div>
                <flux:button
                    href="{{ route('portal.devices.index', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}"
                    variant="ghost" size="sm">
                    <flux:icon.arrow-left class="w-4 h-4" />
                    Back to Devices
                </flux:button>
            </div>

            {{-- Device Information Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg">
                            <flux:icon.device-phone-mobile class="w-8 h-8 text-white" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                                {{ $device->brand }} {{ $device->model }}
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400">{{ $device->type }}</p>
                        </div>
                    </div>

                    <div class="text-right text-sm text-gray-500 dark:text-gray-400">
                        <div>Registered {{ $device->created_at->format('M d, Y') }}</div>
                    </div>
                </div>

                <flux:separator class="my-6" />

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @if ($device->serial_number)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Serial
                                Number</h3>
                            <p class="text-gray-900 dark:text-white font-mono">{{ $device->serial_number }}</p>
                        </div>
                    @endif

                    @if ($device->imei)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">IMEI</h3>
                            <p class="text-gray-900 dark:text-white font-mono">{{ $device->imei }}</p>
                        </div>
                    @endif

                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Total Repairs
                        </h3>
                        <flux:badge variant="info" size="lg">
                            {{ $device->tickets->count() }}
                        </flux:badge>
                    </div>
                </div>

                @if ($device->notes)
                    <flux:separator class="my-6" />

                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Notes</h3>
                        <p class="text-gray-900 dark:text-white">{{ $device->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Repair History --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Repair History</h2>

                @if ($device->tickets->count() > 0)
                    <div class="space-y-4">
                        @foreach ($device->tickets->sortByDesc('created_at') as $ticket)
                            <div
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-purple-500 dark:hover:border-purple-400 transition">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                                Ticket #{{ $ticket->ticket_number }}
                                            </h3>
                                            <flux:badge
                                                :variant="match($ticket->status->value) {
                                                                                                    'pending' => 'warning',
                                                                                                    'in_progress' => 'info',
                                                                                                    'completed' => 'success',
                                                                                                    'on_hold' => 'secondary',
                                                                                                    'cancelled' => 'danger',
                                                                                                    default => 'secondary'
                                                                                                }">
                                                {{ str($ticket->status->value)->replace('_', ' ')->title() }}
                                            </flux:badge>
                                            <flux:badge
                                                variant="{{ match ($ticket->priority->value) {
                                                    'high' => 'danger',
                                                    'medium' => 'warning',
                                                    'low' => 'secondary',
                                                    default => 'secondary',
                                                } }}">
                                                {{ str($ticket->priority->value)->title() }} Priority
                                            </flux:badge>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                            {{ $ticket->problem_description }}
                                        </p>
                                        <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                            <span>
                                                <flux:icon.calendar class="w-4 h-4 inline mr-1" />
                                                {{ $ticket->created_at->format('M d, Y') }}
                                            </span>
                                            @if ($ticket->assignedTo)
                                                <span>
                                                    <flux:icon.user class="w-4 h-4 inline mr-1" />
                                                    {{ $ticket->assignedTo->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-end gap-2">
                                        @if ($ticket->invoice)
                                            <div class="text-right">
                                                <div class="text-sm text-gray-500 dark:text-gray-400">Invoice Total
                                                </div>
                                                <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                                    GH₵ {{ number_format($ticket->invoice->total, 2) }}
                                                </div>
                                            </div>
                                        @endif
                                        <flux:button
                                            href="{{ route('portal.tickets.show', ['customer' => $customer->id, 'token' => $customer->portal_access_token, 'ticket' => $ticket->id]) }}"
                                            variant="outline" size="sm">
                                            View Details
                                        </flux:button>
                                    </div>
                                </div>

                                @if ($ticket->invoice)
                                    <div
                                        class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Invoice:</span>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $ticket->invoice->invoice_number }}
                                            </span>
                                            <flux:badge
                                                :variant="match($ticket->invoice->status->value) {
                                                                                                    'paid' => 'success',
                                                                                                    'pending' => 'warning',
                                                                                                    'overdue' => 'danger',
                                                                                                    'draft' => 'secondary',
                                                                                                    'cancelled' => 'danger',
                                                                                                    default => 'secondary'
                                                                                                }">
                                                {{ str($ticket->invoice->status->value)->title() }}
                                            </flux:badge>
                                        </div>

                                        @if ($ticket->invoice->balance_due > 0)
                                            <span class="text-sm text-orange-600 dark:text-orange-400 font-medium">
                                                Balance Due: GH₵ {{ number_format($ticket->invoice->balance_due, 2) }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <flux:icon.wrench-screwdriver class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Repair History</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            This device hasn't been serviced yet.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </x-layouts.portal-content>
</div>
