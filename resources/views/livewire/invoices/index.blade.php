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

    {{-- Invoices Table --}}
    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
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
