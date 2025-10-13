<div>
    <x-layouts.portal-content :customer="$customer" title="Loyalty Dashboard">
        <div class="space-y-6">
            {{-- Welcome Section --}}
            <div>
                <flux:heading size="xl" class="mb-2">Welcome back, {{ $customer->first_name }}!</flux:heading>
                <flux:text>Track your points, rewards, and loyalty progress.</flux:text>
            </div>

            {{-- Stats Grid --}}
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                {{-- Total Points --}}
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="mb-2 flex items-center justify-between">
                        <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Points</flux:text>
                        <div class="rounded-lg bg-purple-50 p-2 dark:bg-purple-900/20">
                            <flux:icon.sparkles class="size-5 text-purple-500" />
                        </div>
                    </div>
                    <flux:heading size="2xl" class="mb-1">{{ number_format($account->total_points) }}
                    </flux:heading>
                    <flux:text class="text-xs text-zinc-500">Available to redeem</flux:text>
                </div>

                {{-- Lifetime Points --}}
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="mb-2 flex items-center justify-between">
                        <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Lifetime Points
                        </flux:text>
                        <div class="rounded-lg bg-blue-50 p-2 dark:bg-blue-900/20">
                            <flux:icon.chart-bar class="size-5 text-blue-500" />
                        </div>
                    </div>
                    <flux:heading size="2xl" class="mb-1">{{ number_format($account->lifetime_points) }}
                    </flux:heading>
                    <flux:text class="text-xs text-zinc-500">Total earned ever</flux:text>
                </div>

                {{-- Current Tier --}}
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="mb-2 flex items-center justify-between">
                        <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Current Tier</flux:text>
                        <div class="rounded-lg bg-amber-50 p-2 dark:bg-amber-900/20">
                            <flux:icon.star class="size-5 text-amber-500" />
                        </div>
                    </div>
                    <flux:heading size="2xl" class="mb-1">{{ $account->loyaltyTier?->name ?? 'No Tier' }}
                    </flux:heading>
                    <flux:text class="text-xs text-zinc-500">
                        @if ($account->loyaltyTier)
                            {{ $account->loyaltyTier->discount_percentage }}% discount
                        @else
                            Start earning to unlock tiers
                        @endif
                    </flux:text>
                </div>

                {{-- Available Rewards --}}
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="mb-2 flex items-center justify-between">
                        <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Available Rewards
                        </flux:text>
                        <div class="rounded-lg bg-green-50 p-2 dark:bg-green-900/20">
                            <flux:icon.gift class="size-5 text-green-500" />
                        </div>
                    </div>
                    <flux:heading size="2xl" class="mb-1">{{ $availableRewards->count() }}</flux:heading>
                    <flux:text class="text-xs text-zinc-500">Ready to redeem</flux:text>
                </div>
            </div>

            {{-- Tier Progress --}}
            @if ($nextTier)
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <flux:heading size="lg" class="mb-1">Progress to {{ $nextTier->name }}</flux:heading>
                            <flux:text class="text-sm text-zinc-500">
                                {{ number_format($nextTier->min_points - $account->total_points) }} more points needed
                            </flux:text>
                        </div>
                        <flux:badge color="purple" size="lg">
                            {{ number_format($progress, 0) }}%
                        </flux:badge>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="relative h-4 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                        <div class="h-full bg-gradient-to-r from-purple-500 to-purple-600 transition-all duration-500"
                            style="width: {{ $progress }}%">
                        </div>
                    </div>

                    <div class="mt-2 flex justify-between text-xs text-zinc-500">
                        <span>{{ number_format($account->total_points) }} points</span>
                        <span>{{ number_format($nextTier->min_points) }} points</span>
                    </div>
                </div>
            @endif

            {{-- Quick Actions Grid --}}
            <div class="grid gap-6 lg:grid-cols-2">
                {{-- Available Rewards --}}
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="mb-4 flex items-center justify-between">
                        <flux:heading size="lg">Available Rewards</flux:heading>
                        <flux:button variant="ghost" size="sm"
                            href="{{ route('portal.loyalty.rewards', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}">
                            View All →
                        </flux:button>
                    </div>

                    @if ($availableRewards->count() > 0)
                        <div class="space-y-3">
                            @foreach ($availableRewards as $reward)
                                <div
                                    class="flex items-center justify-between rounded-lg border border-zinc-100 p-4 dark:border-zinc-700">
                                    <div class="flex-1">
                                        <flux:heading size="sm" class="mb-1">{{ $reward->name }}</flux:heading>
                                        <flux:text class="text-xs text-zinc-500">
                                            {{ number_format($reward->points_required) }} points
                                        </flux:text>
                                    </div>
                                    <flux:badge color="{{ $reward->type->value === 'discount' ? 'green' : 'purple' }}">
                                        {{ $reward->type->label() }}
                                    </flux:badge>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center">
                            <flux:icon.gift class="mx-auto mb-3 size-12 text-zinc-300 dark:text-zinc-600" />
                            <flux:text class="text-zinc-500">No rewards available yet</flux:text>
                        </div>
                    @endif
                </div>

                {{-- Recent Activity --}}
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="mb-4 flex items-center justify-between">
                        <flux:heading size="lg">Recent Activity</flux:heading>
                        <flux:button variant="ghost" size="sm"
                            href="{{ route('portal.loyalty.history', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}">
                            View All →
                        </flux:button>
                    </div>

                    @if ($recentTransactions->count() > 0)
                        <div class="space-y-3">
                            @foreach ($recentTransactions as $transaction)
                                <div
                                    class="flex items-center justify-between border-b border-zinc-100 pb-3 last:border-0 dark:border-zinc-700">
                                    <div class="flex-1">
                                        <flux:text class="mb-1 text-sm font-medium">{{ $transaction->description }}
                                        </flux:text>
                                        <flux:text class="text-xs text-zinc-500">
                                            {{ $transaction->created_at->diffForHumans() }}
                                        </flux:text>
                                    </div>
                                    <flux:heading size="sm"
                                        class="{{ $transaction->points >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $transaction->points >= 0 ? '+' : '' }}{{ number_format($transaction->points) }}
                                    </flux:heading>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center">
                            <flux:icon.clock class="mx-auto mb-3 size-12 text-zinc-300 dark:text-zinc-600" />
                            <flux:text class="text-zinc-500">No activity yet</flux:text>
                        </div>
                    @endif
                </div>
            </div>

            {{-- How to Earn Points --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">How to Earn Points</flux:heading>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="flex items-start gap-3">
                        <div class="rounded-lg bg-purple-50 p-2 dark:bg-purple-900/20">
                            <flux:icon.shopping-cart class="size-5 text-purple-500" />
                        </div>
                        <div>
                            <flux:heading size="sm" class="mb-1">Make Purchases</flux:heading>
                            <flux:text class="text-sm text-zinc-500">Earn 1 point for every $1 spent</flux:text>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="rounded-lg bg-blue-50 p-2 dark:bg-blue-900/20">
                            <flux:icon.star class="size-5 text-blue-500" />
                        </div>
                        <div>
                            <flux:heading size="sm" class="mb-1">Tier Bonuses</flux:heading>
                            <flux:text class="text-sm text-zinc-500">Higher tiers earn more points per purchase
                            </flux:text>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="rounded-lg bg-amber-50 p-2 dark:bg-amber-900/20">
                            <flux:icon.gift class="size-5 text-amber-500" />
                        </div>
                        <div>
                            <flux:heading size="sm" class="mb-1">Redeem Rewards</flux:heading>
                            <flux:text class="text-sm text-zinc-500">Use your points for discounts and vouchers
                            </flux:text>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-layouts.portal-content>
</div>
