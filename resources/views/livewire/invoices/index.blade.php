<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" class="mb-2">{{ __('Invoices') }}</flux:heading>
                <flux:text>{{ __('Manage customer invoices and payments') }}</flux:text>
            </div>
            @can('create', App\Models\Invoice::class)
                <flux:button :href="route('invoices.create')" wire:navigate>
                    <flux:icon.plus class="-ml-1 mr-2 size-5" />
                    {{ __('Create Invoice') }}
                </flux:button>
            @endcan
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="mb-6 space-y-4">
        <div class="grid gap-4 sm:grid-cols-2">
            <flux:input wire:model.live.debounce.300ms="search"
                placeholder="{{ __('Search by invoice number, customer, or ticket...') }}" />

            <select wire:model.live="status"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="pending">{{ __('Pending') }}</option>
                <option value="paid">{{ __('Paid') }}</option>
                <option value="cancelled">{{ __('Cancelled') }}</option>
            </select>
        </div>

        @if ($search || $status)
            <div class="flex justify-end">
                <flux:button variant="ghost" size="sm" wire:click="clearFilters">
                    {{ __('Clear filters') }}
                </flux:button>
            </div>
        @endif
    </div>

    {{-- Desktop Invoices Table (hidden on mobile) --}}
    <div
        class="hidden overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800 lg:block">
        @if ($invoices->isEmpty())
            <div class="p-6 text-center">
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    @if ($search || $status)
                        {{ __('No invoices found matching your filters.') }}
                    @else
                        {{ __('No invoices yet.') }}
                    @endif
                </flux:text>
            </div>
        @else
            <table class="w-full">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Invoice #') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Customer') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Ticket') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Total') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Status') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Date') }}
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($invoices as $invoice)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                            <td class="px-6 py-4">
                                <a href="{{ route('invoices.show', $invoice) }}"
                                    class="font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                    wire:navigate>
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $invoice->customer->full_name }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                <a href="{{ route('tickets.show', $invoice->ticket) }}"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                    wire:navigate>
                                    {{ $invoice->ticket->ticket_number }}
                                </a>
                            </td>
                            <td
                                class="whitespace-nowrap px-6 py-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                ${{ number_format($invoice->total, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <flux:badge :color="$invoice->status->color()" size="sm">
                                    {{ $invoice->status->label() }}
                                </flux:badge>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $invoice->created_at->format('M d, Y') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @can('update', $invoice)
                                        <a href="{{ route('invoices.edit', $invoice) }}" wire:navigate
                                            class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white"
                                            title="Edit">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endcan

                                    @can('delete', $invoice)
                                        <button wire:click="confirmDelete('{{ $invoice->id }}')"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            title="Delete">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

    {{-- Mobile Card View (visible on mobile) --}}
    <div class="space-y-4 lg:hidden">
        @if ($invoices->isEmpty())
            <div
                class="rounded-lg border border-zinc-200 bg-white p-6 text-center dark:border-zinc-700 dark:bg-zinc-800">
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    @if ($search || $status)
                        {{ __('No invoices found matching your filters.') }}
                    @else
                        {{ __('No invoices yet.') }}
                    @endif
                </flux:text>
            </div>
        @else
            @foreach ($invoices as $invoice)
                <div
                    class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm transition-shadow hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800">
                    <!-- Invoice Header -->
                    <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <a href="{{ route('invoices.show', $invoice) }}"
                                    class="text-sm font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                    wire:navigate>
                                    {{ $invoice->invoice_number }}
                                </a>
                                <div class="mt-2">
                                    <flux:badge :color="$invoice->status->color()" size="sm">
                                        {{ $invoice->status->label() }}
                                    </flux:badge>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-semibold text-zinc-900 dark:text-white">
                                    ${{ number_format($invoice->total, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Details -->
                    <div class="px-4 py-3">
                        <dl class="space-y-2.5">
                            <div class="flex items-center justify-between text-sm">
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Customer</dt>
                                <dd class="text-zinc-900 dark:text-white">{{ $invoice->customer->full_name }}</dd>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Ticket</dt>
                                <dd class="text-zinc-900 dark:text-white">
                                    <a href="{{ route('tickets.show', $invoice->ticket) }}"
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                        wire:navigate>
                                        {{ $invoice->ticket->ticket_number }}
                                    </a>
                                </dd>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Date</dt>
                                <dd class="text-zinc-900 dark:text-white">{{ $invoice->created_at->format('M d, Y') }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Actions -->
                    <div class="border-t border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-center justify-end gap-3">
                            @can('update', $invoice)
                                <a href="{{ route('invoices.edit', $invoice) }}" wire:navigate
                                    class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </a>
                            @endcan

                            @can('delete', $invoice)
                                <button wire:click="confirmDelete('{{ $invoice->id }}')"
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
            @endforeach

            <div class="rounded-lg border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-confirmation" :show="$deletingInvoiceId !== null" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete Invoice') }}</flux:heading>
                <flux:subheading class="mt-2">
                    {{ __('Are you sure you want to delete this invoice? This action cannot be undone.') }}
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="cancelDelete">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="danger" wire:click="delete">
                    {{ __('Delete Invoice') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
