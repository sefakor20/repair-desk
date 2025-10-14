<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total Referrals</p>
                    <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ $stats['total'] }}</p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                    <svg class="size-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Completed</p>
                    <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ $stats['completed'] }}</p>
                </div>
                <div class="p-3 bg-green-100 dark:bg-green-900/20 rounded-lg">
                    <svg class="size-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Pending</p>
                    <p class="mt-2 text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['pending'] }}</p>
                </div>
                <div class="p-3 bg-amber-100 dark:bg-amber-900/20 rounded-lg">
                    <svg class="size-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Points Earned</p>
                    <p class="mt-2 text-3xl font-bold text-purple-600 dark:text-purple-400">
                        {{ number_format($stats['points_earned']) }}</p>
                </div>
                <div class="p-3 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                    <svg class="size-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Referral Code Card -->
    <div
        class="bg-gradient-to-br from-blue-600 to-purple-600 dark:from-blue-700 dark:to-purple-700 rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="text-white">
                    <h3 class="text-2xl font-bold">Your Referral Code</h3>
                    <p class="mt-2 text-blue-100">Share this code with friends and earn rewards!</p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="bg-white/20 backdrop-blur-sm rounded-lg px-6 py-3 border border-white/30">
                        <code class="text-2xl font-mono font-bold text-white tracking-wider">{{ $referralCode }}</code>
                    </div>

                    <flux:button variant="ghost"
                        class="!bg-white/20 hover:!bg-white/30 !text-white border border-white/30" wire:click="copyCode"
                        x-on:click="navigator.clipboard.writeText('{{ $referralCode }}')">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </flux:button>

                    <flux:button variant="ghost" class="!bg-white !text-blue-600 hover:!bg-blue-50"
                        wire:click="openInviteModal">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Invite Friend
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    <!-- Referrals List -->
    <div
        class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-zinc-200 dark:border-zinc-800">
            <flux:heading size="lg">Your Referrals</flux:heading>
        </div>

        @if ($referrals->count() > 0)
            <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @foreach ($referrals as $referral)
                    <div class="px-6 py-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="size-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white font-semibold text-sm">
                                            {{ strtoupper(substr($referral->referred_name ?? $referral->referred_email, 0, 2)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-medium text-zinc-900 dark:text-white">
                                            {{ $referral->referred?->full_name ?? ($referral->referred_name ?? 'Pending') }}
                                        </p>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ $referral->referred_email }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                @if ($referral->status === 'completed')
                                    <flux:badge color="green">
                                        <svg class="size-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Completed - {{ number_format($referral->points_awarded) }} pts
                                    </flux:badge>
                                @elseif($referral->status === 'pending')
                                    <flux:badge color="amber">
                                        <svg class="size-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        Pending
                                    </flux:badge>
                                @else
                                    <flux:badge color="zinc">{{ ucfirst($referral->status) }}</flux:badge>
                                @endif

                                <time class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $referral->created_at->diffForHumans() }}
                                </time>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-800">
                {{ $referrals->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto size-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">No referrals yet</h3>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Start sharing your referral code to earn
                    rewards!</p>
                <div class="mt-6">
                    <flux:button wire:click="openInviteModal" variant="primary">
                        Invite Your First Friend
                    </flux:button>
                </div>
            </div>
        @endif
    </div>

    <!-- Invite Modal -->
    <flux:modal :open="$showInviteModal" wire:model="showInviteModal">
        <form wire:submit="sendInvite" class="space-y-6">
            <div>
                <flux:heading size="lg">Invite a Friend</flux:heading>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    Send an invitation to a friend and earn rewards when they join!
                </p>
            </div>

            <flux:field>
                <flux:label>Friend's Email</flux:label>
                <flux:input type="email" wire:model="friend_email" placeholder="friend@example.com" required />
                <flux:error name="friend_email" />
            </flux:field>

            <flux:field>
                <flux:label>Friend's Name (Optional)</flux:label>
                <flux:input wire:model="friend_name" placeholder="John Doe" />
                <flux:error name="friend_name" />
            </flux:field>

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeInviteModal" type="button">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="sendInvite">
                    <span wire:loading.remove wire:target="sendInvite">Send Invitation</span>
                    <span wire:loading wire:target="sendInvite">Sending...</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
