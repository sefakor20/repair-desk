<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $ticket->ticket_number }}</h1>
                <x-status-badge :status="$ticket->status" />
                <x-status-badge :status="$ticket->priority" />
            </div>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Created
                {{ $ticket->created_at->format('M d, Y') }} by {{ $ticket->createdBy->name }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('tickets.index') }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Tickets
            </a>

            @can('update', $ticket)
                <a href="{{ route('tickets.edit', $ticket) }}" wire:navigate
                    class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Ticket
                </a>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Ticket Details Card -->
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Ticket Details</h2>

                <div class="space-y-4">
                    <!-- Customer Info -->
                    @if ($ticket->customer)
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Customer</h3>
                            <div class="mt-1">
                                <a href="{{ route('customers.show', $ticket->customer) }}" wire:navigate
                                    class="text-zinc-900 hover:text-zinc-700 dark:text-white dark:hover:text-zinc-300">
                                    {{ $ticket->customer->full_name }}
                                </a>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $ticket->customer->email }}</p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $ticket->customer->phone }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Device Info -->
                    @if ($ticket->device)
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Device</h3>
                            <p class="mt-1 text-zinc-900 dark:text-white">{{ $ticket->device->device_name }}</p>
                            @if ($ticket->device->serial_number)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">S/N:
                                    {{ $ticket->device->serial_number }}
                                </p>
                            @endif
                            @if ($ticket->device->imei)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">IMEI: {{ $ticket->device->imei }}
                                </p>
                            @endif
                        </div>
                    @endif

                    <hr class="border-zinc-200 dark:border-zinc-700" />

                    <!-- Problem Description -->
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Problem Description</h3>
                        <p class="mt-1 whitespace-pre-wrap text-zinc-900 dark:text-white">
                            {{ $ticket->problem_description }}
                        </p>
                    </div>

                    <!-- Diagnosis -->
                    @if ($ticket->diagnosis)
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Diagnosis</h3>
                            <p class="mt-1 whitespace-pre-wrap text-zinc-900 dark:text-white">{{ $ticket->diagnosis }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Parts Used -->
            @if ($ticket->parts->isNotEmpty())
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Parts Used</h2>

                    <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-900">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        Part Name</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        Qty</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        Price</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                                @foreach ($ticket->parts as $part)
                                    <tr>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-900 dark:text-white">
                                            {{ $part->part_name }}</td>
                                        <td
                                            class="whitespace-nowrap px-4 py-3 text-right text-sm text-zinc-900 dark:text-white">
                                            {{ $part->quantity }}</td>
                                        <td
                                            class="whitespace-nowrap px-4 py-3 text-right text-sm text-zinc-900 dark:text-white">
                                            {{ format_currency($part->selling_price) }}</td>
                                        <td
                                            class="whitespace-nowrap px-4 py-3 text-right text-sm text-zinc-900 dark:text-white">
                                            {{ format_currency($part->total) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-zinc-50 dark:bg-zinc-900">
                                    <td colspan="3"
                                        class="px-4 py-3 text-right text-sm font-semibold text-zinc-900 dark:text-white">
                                        Parts Total:</td>
                                    <td
                                        class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ format_currency($ticket->parts->sum('total')) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Notes & Timeline -->
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Timeline & Notes</h2>

                @if ($ticket->notes->isEmpty())
                    <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">No notes yet</p>
                @else
                    <div class="space-y-4">
                        @foreach ($ticket->notes->sortByDesc('created_at') as $note)
                            <div class="flex gap-4">
                                <div
                                    class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-zinc-200 text-sm font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                                    {{ $note->user->initials() }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span
                                                class="font-medium text-zinc-900 dark:text-white">{{ $note->user->name }}</span>
                                            @if ($note->is_internal)
                                                <span
                                                    class="ml-2 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                                    Internal
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $note->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    <p class="mt-1 whitespace-pre-wrap text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ $note->note }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status Card -->
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Status</h2>

                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Current Status</p>
                        <div class="mt-1">
                            <x-status-badge :status="$ticket->status" />
                        </div>
                    </div>

                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Priority</p>
                        <div class="mt-1">
                            <x-status-badge :status="$ticket->priority" />
                        </div>
                    </div>

                    @if ($ticket->assignedTo)
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Assigned To</p>
                            <div class="mt-1 flex items-center gap-2">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-200 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                                    {{ $ticket->assignedTo->initials() }}
                                </div>
                                <span
                                    class="text-sm text-zinc-900 dark:text-white">{{ $ticket->assignedTo->name }}</span>
                            </div>
                        </div>
                    @else
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Assigned To</p>
                            <p class="mt-1 text-sm text-zinc-400 dark:text-zinc-500">Unassigned</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Dates Card -->
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Important Dates</h2>

                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Created</p>
                        <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                            {{ $ticket->created_at->format('M d, Y g:i A') }}
                        </p>
                    </div>

                    @if ($ticket->estimated_completion)
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Estimated Completion</p>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                {{ $ticket->estimated_completion->format('M d, Y') }}</p>
                        </div>
                    @endif

                    @if ($ticket->actual_completion)
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Completed</p>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                {{ $ticket->actual_completion->format('M d, Y g:i A') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Invoice Card -->
            @if ($ticket->invoice)
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Invoice</h2>

                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Invoice Number</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">
                                {{ $ticket->invoice->invoice_number }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Status</p>
                            <div class="mt-1">
                                <x-status-badge :status="$ticket->invoice->status" />
                            </div>
                        </div>

                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Amount</p>
                            <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                                {{ format_currency($ticket->invoice->total) }}</p>
                        </div>

                        @if ($ticket->invoice->payments->isNotEmpty())
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Paid</p>
                                <p class="mt-1 font-medium text-green-600 dark:text-green-400">
                                    {{ format_currency($ticket->invoice->payments->sum('amount')) }}</p>
                            </div>

                            @php
                                $balance = $ticket->invoice->total - $ticket->invoice->payments->sum('amount');
                            @endphp

                            @if ($balance > 0)
                                <div>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Balance Due</p>
                                    <p class="mt-1 font-medium text-red-600 dark:text-red-400">
                                        {{ format_currency($balance) }}</p>
                                </div>
                            @endif
                        @endif

                        @can('view', $ticket->invoice)
                            <a href="#" wire:navigate
                                class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                View Invoice
                            </a>
                        @endcan
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
