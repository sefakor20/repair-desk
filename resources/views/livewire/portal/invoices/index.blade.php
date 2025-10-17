<div>
    <x-layouts.portal-content :customer="$customer" title="My Invoices">
        <div class="space-y-6">
            {{-- Header --}}
            <div>
                <flux:heading size="xl" class="mb-2">My Invoices</flux:heading>
                <flux:text>View and manage your repair invoices</flux:text>
            </div>

            {{-- Filters --}}
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Search invoices..." type="search" />
                </div>

                <div class="w-full sm:w-48">
                    <flux:select wire:model.live="filterStatus">
                        <option value="all">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="overdue">Overdue</option>
                        <option value="cancelled">Cancelled</option>
                    </flux:select>
                </div>

                @if($search || $filterStatus !== 'all')
                    <flux:button wire:click="clearFilters" variant="ghost">
                        Clear Filters
                    </flux:button>
                @endif
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm mb-1">Total Invoices</p>
                            <p class="text-3xl font-bold">{{ $customer->invoices()->count() }}</p>
                        </div>
                        <flux:icon.document-text class="w-12 h-12 text-blue-200 opacity-50" />
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm mb-1">Paid</p>
                            <p class="text-3xl font-bold">GH₵ {{ number_format($customer->invoices()->where('status', 'paid')->sum('total'), 2) }}</p>
                        </div>
                        <flux:icon.check-circle class="w-12 h-12 text-green-200 opacity-50" />
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm mb-1">Pending</p>
                            <p class="text-3xl font-bold">GH₵ {{ number_format($customer->invoices()->where('status', 'pending')->sum('total'), 2) }}</p>
                        </div>
                        <flux:icon.clock class="w-12 h-12 text-orange-200 opacity-50" />
                    </div>
                </div>
            </div>

            {{-- Invoices List --}}
            @if ($invoices->count() > 0)
                <div class="space-y-4">
                    @foreach ($invoices as $invoice)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-md transition">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                            Invoice #{{ $invoice->invoice_number }}
                                        </h3>
                                        <flux:badge
                                            :variant="match($invoice->status->value) {
                                                'draft' => 'secondary',
                                                'pending' => 'warning',
                                                'paid' => 'success',
                                                'overdue' => 'danger',
                                                'cancelled' => 'secondary',
                                                default => 'secondary'
                                            }">
                                            {{ str($invoice->status->label())->title() }}
                                        </flux:badge>
                                    </div>

                                    @if ($invoice->ticket)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                            Ticket #{{ $invoice->ticket->ticket_number }}
                                            @if($invoice->ticket->device)
                                                - {{ $invoice->ticket->device->brand }} {{ $invoice->ticket->device->model }}
                                            @endif
                                        </p>
                                    @endif

                                    <div class="flex flex-wrap gap-6 text-sm">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Amount:</span>
                                            <span class="font-semibold text-gray-900 dark:text-white ml-1">
                                                GH₵ {{ number_format($invoice->total, 2) }}
                                            </span>
                                        </div>

                                        @if($invoice->payments->count() > 0)
                                            <div>
                                                <span class="text-gray-500 dark:text-gray-400">Paid:</span>
                                                <span class="font-semibold text-green-600 dark:text-green-400 ml-1">
                                                    GH₵ {{ number_format($invoice->payments->sum('amount'), 2) }}
                                                </span>
                                            </div>
                                        @endif

                                        @if($invoice->balance_due > 0)
                                            <div>
                                                <span class="text-gray-500 dark:text-gray-400">Balance:</span>
                                                <span class="font-semibold text-orange-600 dark:text-orange-400 ml-1">
                                                    GH₵ {{ number_format($invoice->balance_due, 2) }}
                                                </span>
                                            </div>
                                        @endif

                                        <div class="flex items-center gap-1 text-gray-500 dark:text-gray-400">
                                            <flux:icon.calendar class="w-4 h-4" />
                                            <span>{{ $invoice->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    @if($invoice->status->value === 'pending' && $invoice->balance_due > 0)
                                        <flux:button variant="primary" size="sm">
                                            Pay Now
                                        </flux:button>
                                    @endif
                                    
                                    <flux:button variant="ghost" size="sm">
                                        <flux:icon.arrow-down-tray class="w-4 h-4" />
                                        Download
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $invoices->links() }}
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                    <flux:icon.document-text class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No invoices found</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        @if ($search || $filterStatus !== 'all')
                            Try adjusting your filters to find what you're looking for.
                        @else
                            You don't have any invoices yet.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </x-layouts.portal-content>
</div>
