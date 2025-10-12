<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Returns & Refunds</h1>
    </div>

    {{-- Stats Dashboard --}}
    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pending Returns</p>
            <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ $this->stats['pending_count'] }}
            </p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Approved Returns</p>
            <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ $this->stats['approved_count'] }}
            </p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Completed Returns</p>
            <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ $this->stats['completed_count'] }}
            </p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Refunded</p>
            <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">
                {{ format_currency($this->stats['total_refunded']) }}
            </p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex-1">
                <flux:input type="search" wire:model.live.debounce.300ms="search"
                    placeholder="Search returns by number, sale, or customer..." />
            </div>
            <div class="flex gap-2">
                <flux:button variant="{{ $status === 'all' ? 'primary' : 'ghost' }}" wire:click="$set('status', 'all')">
                    All
                </flux:button>
                <flux:button variant="{{ $status === 'pending' ? 'primary' : 'ghost' }}"
                    wire:click="$set('status', 'pending')">
                    Pending
                </flux:button>
                <flux:button variant="{{ $status === 'approved' ? 'primary' : 'ghost' }}"
                    wire:click="$set('status', 'approved')">
                    Approved
                </flux:button>
                <flux:button variant="{{ $status === 'completed' ? 'primary' : 'ghost' }}"
                    wire:click="$set('status', 'completed')">
                    Completed
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Returns Table --}}
    <div class="rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                    <tr>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Return #</th>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Sale #</th>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Customer</th>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Date</th>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Items</th>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Refund Amount</th>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Status</th>
                        <th
                            class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($this->returns as $return)
                        <tr wire:key="return-{{ $return->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-900">
                            <td class="px-4 py-3">
                                <span class="font-mono text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $return->return_number }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('pos.show', $return->originalSale) }}"
                                    class="font-mono text-sm text-blue-600 hover:underline dark:text-blue-400"
                                    wire:navigate>
                                    {{ $return->originalSale->sale_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-zinc-900 dark:text-zinc-100">
                                    {{ $return->customer ? $return->customer->full_name : 'Walk-in' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $return->created_at->format('M d, Y') }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-zinc-900 dark:text-zinc-100">
                                    {{ $return->items_count }} {{ Str::plural('item', $return->items_count) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                    {{ format_currency($return->total_refund_amount) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge variant="{{ $return->status->color() }}">
                                    {{ $return->status->label() }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    @if ($return->status === App\Enums\ReturnStatus::Pending)
                                        <flux:button variant="primary" size="sm"
                                            wire:click="approveReturn('{{ $return->id }}')"
                                            wire:confirm="Are you sure you want to approve this return?">
                                            Approve
                                        </flux:button>
                                        <flux:button variant="danger" size="sm"
                                            wire:click="rejectReturn('{{ $return->id }}')"
                                            wire:confirm="Are you sure you want to reject this return?">
                                            Reject
                                        </flux:button>
                                    @endif
                                    <flux:button variant="ghost" size="sm"
                                        href="{{ route('pos.show', $return->originalSale) }}" wire:navigate>
                                        View Sale
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-12 w-12 text-zinc-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <p class="mt-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">No returns
                                        found</p>
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                        @if ($search || $status !== 'all')
                                            Try adjusting your filters
                                        @else
                                            Returns will appear here when customers return items
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->returns->hasPages())
            <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
                {{ $this->returns->links() }}
            </div>
        @endif
    </div>
</div>
