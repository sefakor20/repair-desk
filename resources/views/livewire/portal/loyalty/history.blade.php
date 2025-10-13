<x-layouts.portal :customer="$customer" title="Points History">
    <div class="space-y-6">
        <div>
            <flux:heading size="xl" class="mb-2">Points History</flux:heading>
            <flux:text>View all your points earned and redeemed</flux:text>
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap items-center gap-3">
            <flux:button variant="{{ $filterType === 'all' ? 'primary' : 'ghost' }}"
                wire:click="$set('filterType', 'all')">
                All
            </flux:button>
            <flux:button variant="{{ $filterType === 'earned' ? 'primary' : 'ghost' }}"
                wire:click="$set('filterType', 'earned')">
                Earned
            </flux:button>
            <flux:button variant="{{ $filterType === 'redeemed' ? 'primary' : 'ghost' }}"
                wire:click="$set('filterType', 'redeemed')">
                Redeemed
            </flux:button>

            @if ($filterType !== 'all')
                <flux:button variant="ghost" wire:click="clearFilters">
                    Clear Filters
                </flux:button>
            @endif
        </div>

        {{-- Transactions Table --}}
        <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-zinc-500">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-zinc-500">Description</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase text-zinc-500">Points</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase text-zinc-500">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse($transactions as $transaction)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <flux:text class="text-sm font-medium">
                                        {{ $transaction->created_at->format('M d, Y') }}
                                    </flux:text>
                                    <flux:text class="text-xs text-zinc-500">
                                        {{ $transaction->created_at->format('h:i A') }}
                                    </flux:text>
                                </td>
                                <td class="px-6 py-4">
                                    <flux:text class="text-sm">{{ $transaction->description }}</flux:text>
                                    <flux:text class="text-xs text-zinc-500">
                                        {{ ucfirst($transaction->type) }}
                                    </flux:text>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <flux:heading size="sm"
                                        class="{{ $transaction->points >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $transaction->points >= 0 ? '+' : '' }}{{ number_format($transaction->points) }}
                                    </flux:heading>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <flux:text class="text-sm font-medium">
                                        {{ number_format($transaction->balance_after) }}
                                    </flux:text>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-16 text-center">
                                    <flux:icon.clock class="mx-auto mb-3 size-12 text-zinc-300 dark:text-zinc-600" />
                                    <flux:heading size="sm" class="mb-2">No transactions found</flux:heading>
                                    <flux:text class="text-zinc-500">Your points activity will appear here</flux:text>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $transactions->links() }}
    </div>
</x-layouts.portal>
