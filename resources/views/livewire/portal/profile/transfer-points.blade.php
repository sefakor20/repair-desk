<div class="space-y-6">
    <!-- Balance Card -->
    <div
        class="bg-gradient-to-br from-purple-600 to-blue-600 dark:from-purple-700 dark:to-blue-700 rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="text-white">
                    <p class="text-sm font-medium text-purple-100">Available Balance</p>
                    <p class="mt-2 text-4xl font-bold">{{ number_format($customer->loyaltyAccount->total_points) }}</p>
                    <p class="mt-1 text-sm text-purple-100">points</p>
                </div>

                <flux:button variant="ghost" class="!bg-white !text-purple-600 hover:!bg-purple-50"
                    wire:click="openTransferModal">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Transfer Points
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <flux:callout variant="info">
        <div class="flex items-start gap-3">
            <svg class="size-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-sm">
                <p class="font-medium text-zinc-900 dark:text-white">About Point Transfers</p>
                <p class="mt-1 text-zinc-600 dark:text-zinc-400">
                    You can gift your points to other customers. The minimum transfer is 50 points. Transfers are
                    instant and cannot be reversed.
                </p>
            </div>
        </div>
    </flux:callout>

    <!-- Transfer History -->
    <div
        class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-zinc-200 dark:border-zinc-800">
            <flux:heading size="lg">Transfer History</flux:heading>
        </div>

        @if ($transfers->count() > 0)
            <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @foreach ($transfers as $transfer)
                    <div class="px-6 py-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex items-center gap-4">
                                <!-- Direction Icon -->
                                <div class="flex-shrink-0">
                                    @if ($transfer->sender_id === $customer->id)
                                        <div
                                            class="size-10 rounded-full bg-red-100 dark:bg-red-900/20 flex items-center justify-center">
                                            <svg class="size-5 text-red-600 dark:text-red-400" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 11l5-5m0 0l5 5m-5-5v12" />
                                            </svg>
                                        </div>
                                    @else
                                        <div
                                            class="size-10 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                                            <svg class="size-5 text-green-600 dark:text-green-400" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Transfer Details -->
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="font-medium text-zinc-900 dark:text-white">
                                            @if ($transfer->sender_id === $customer->id)
                                                Sent to {{ $transfer->recipient->full_name }}
                                            @else
                                                Received from {{ $transfer->sender->full_name }}
                                            @endif
                                        </p>
                                    </div>

                                    @if ($transfer->message)
                                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                            "{{ $transfer->message }}"
                                        </p>
                                    @endif

                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-500">
                                        {{ $transfer->created_at->format('M j, Y g:i A') }}
                                    </p>
                                </div>

                                <!-- Points Amount -->
                                <div class="text-right">
                                    <p
                                        class="text-lg font-bold {{ $transfer->sender_id === $customer->id ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                        {{ $transfer->sender_id === $customer->id ? '-' : '+' }}{{ number_format($transfer->points) }}
                                    </p>

                                    @if ($transfer->status === 'completed')
                                        <flux:badge color="green" size="sm">Completed</flux:badge>
                                    @elseif($transfer->status === 'pending')
                                        <flux:badge color="amber" size="sm">Pending</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">{{ ucfirst($transfer->status) }}
                                        </flux:badge>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-800">
                {{ $transfers->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto size-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">No transfers yet</h3>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Start gifting points to friends and family!</p>
                <div class="mt-6">
                    <flux:button wire:click="openTransferModal" variant="primary">
                        Make Your First Transfer
                    </flux:button>
                </div>
            </div>
        @endif
    </div>

    <!-- Transfer Modal -->
    <flux:modal :open="$showTransferModal" wire:model="showTransferModal">
        <form wire:submit="transfer" class="space-y-6">
            <div>
                <flux:heading size="lg">Transfer Points</flux:heading>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    Gift your loyalty points to another customer. Minimum 50 points.
                </p>
            </div>

            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                <p class="text-sm text-blue-900 dark:text-blue-100">
                    <span class="font-semibold">Available:</span>
                    {{ number_format($customer->loyaltyAccount->total_points) }} points
                </p>
            </div>

            <flux:field>
                <flux:label>Recipient Email</flux:label>
                <flux:input type="email" wire:model="recipient_email" placeholder="recipient@example.com" required />
                <flux:error name="recipient_email" />
            </flux:field>

            <flux:field>
                <flux:label>Points to Transfer</flux:label>
                <flux:input type="number" wire:model="points" placeholder="50" min="50"
                    max="{{ $customer->loyaltyAccount->total_points }}" required />
                <flux:error name="points" />
            </flux:field>

            <flux:field>
                <flux:label>Message (Optional)</flux:label>
                <flux:textarea wire:model="message" placeholder="Add a personal message..." rows="3" />
                <flux:error name="message" />
            </flux:field>

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeTransferModal" type="button">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="transfer">
                    <span wire:loading.remove wire:target="transfer">Transfer Points</span>
                    <span wire:loading wire:target="transfer" class="flex items-center gap-2">
                        <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Processing...
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
