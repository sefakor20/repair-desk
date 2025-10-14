<div>
    <x-layouts.portal-content :customer="$customer" title="Rewards Catalog">
        <div class="space-y-6">
            <div>
                <flux:heading size="xl" class="mb-2">Rewards Catalog</flux:heading>
                <flux:text>Browse and redeem rewards with your points</flux:text>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse($rewards as $reward)
                    <div
                        class="group relative overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm transition-all duration-300 hover:scale-105 hover:shadow-xl hover:shadow-purple-500/10 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-purple-500/50">
                        <div class="p-6">
                            <div class="mb-4 flex items-start justify-between">
                                <flux:badge color="{{ $reward->type->value === 'discount' ? 'green' : 'purple' }}">
                                    {{ $reward->type->label() }}
                                </flux:badge>
                                @if ($reward->min_tier_id)
                                    <flux:badge color="amber" size="sm">
                                        {{ $reward->minTier->name }}+
                                    </flux:badge>
                                @endif
                            </div>

                            <flux:heading size="lg" class="mb-2">{{ $reward->name }}</flux:heading>
                            @if ($reward->description)
                                <flux:text class="mb-4 text-sm text-zinc-500">{{ $reward->description }}</flux:text>
                            @endif

                            <div class="mb-4 flex items-center gap-2">
                                <flux:icon.sparkles class="size-5 text-purple-500" />
                                <flux:heading size="xl">{{ number_format($reward->points_required) }}
                                </flux:heading>
                                <flux:text class="text-sm text-zinc-500">points</flux:text>
                            </div>

                            @if ($reward->canBeRedeemedBy($account))
                                <flux:button class="w-full transition-all duration-200 hover:scale-105 active:scale-95"
                                    variant="primary" wire:click="selectReward('{{ $reward->id }}')"
                                    wire:loading.attr="disabled" wire:target="selectReward('{{ $reward->id }}')">
                                    <span wire:loading.remove wire:target="selectReward('{{ $reward->id }}')">Redeem
                                        Now</span>
                                    <span wire:loading
                                        wire:target="selectReward('{{ $reward->id }}')">Loading...</span>
                                </flux:button>
                            @else
                                <flux:button class="w-full" variant="ghost" disabled>
                                    @if ($account->total_points < $reward->points_required)
                                        Need {{ number_format($reward->points_required - $account->total_points) }}
                                        more
                                        points
                                    @else
                                        Not Eligible
                                    @endif
                                </flux:button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 text-center">
                        <flux:icon.gift class="mx-auto mb-4 size-16 text-zinc-300 dark:text-zinc-600" />
                        <flux:heading size="lg" class="mb-2">No rewards available</flux:heading>
                        <flux:text class="text-zinc-500">Check back later for new rewards</flux:text>
                    </div>
                @endforelse
            </div>

            {{ $rewards->links() }}
        </div>

        {{-- Redemption Modal --}}
        @if ($showRedemptionModal && $selectedReward)
            <flux:modal wire:model="showRedemptionModal" class="min-w-md">
                <div class="space-y-4">
                    <div>
                        <flux:heading size="lg" class="mb-2">Confirm Redemption</flux:heading>
                        <flux:text>Are you sure you want to redeem this reward?</flux:text>
                    </div>

                    <div
                        class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 transition-all duration-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900">
                        <flux:heading size="md" class="mb-2">{{ $selectedReward->name }}</flux:heading>
                        <flux:text class="mb-3 text-sm text-zinc-500">{{ $selectedReward->description }}</flux:text>
                        <div class="flex items-center justify-between">
                            <flux:text class="font-medium">Cost:</flux:text>
                            <flux:heading size="lg">{{ number_format($selectedReward->points_required) }} points
                            </flux:heading>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <flux:text class="font-medium">New Balance:</flux:text>
                            <flux:text class="font-medium">
                                {{ number_format($account->total_points - $selectedReward->points_required) }} points
                            </flux:text>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <flux:button variant="ghost" wire:click="closeModal"
                            class="flex-1 transition-all duration-200 hover:scale-105">Cancel</flux:button>
                        <flux:button variant="primary" wire:click="redeemReward"
                            class="flex-1 transition-all duration-200 hover:scale-105" wire:loading.attr="disabled"
                            wire:target="redeemReward">
                            <span wire:loading.remove wire:target="redeemReward">Redeem</span>
                            <span wire:loading wire:target="redeemReward">Processing...</span>
                        </flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif
    </x-layouts.portal-content>
</div>
