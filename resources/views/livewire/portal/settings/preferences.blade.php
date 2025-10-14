<div class="max-w-4xl mx-auto">
    <div
        class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-zinc-200 dark:border-zinc-800">
            <flux:heading size="lg" class="text-zinc-900 dark:text-white">Notification Preferences</flux:heading>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Manage how and when you receive notifications from us
            </p>
        </div>

        <!-- Form -->
        <form wire:submit="save" class="divide-y divide-zinc-200 dark:divide-zinc-800">
            <!-- Loyalty Notifications -->
            <div class="px-6 py-6 space-y-4">
                <div>
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Loyalty Program</h3>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Stay updated on your rewards and points</p>
                </div>

                <div class="space-y-4 mt-4">
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <flux:switch wire:model="notify_points_earned" />
                        <div class="flex-1">
                            <div
                                class="text-sm font-medium text-zinc-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                Points Earned
                            </div>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                Get notified when you earn loyalty points
                            </div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer group">
                        <flux:switch wire:model="notify_reward_available" />
                        <div class="flex-1">
                            <div
                                class="text-sm font-medium text-zinc-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                Reward Available
                            </div>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                Alert me when I have enough points for a reward
                            </div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer group">
                        <flux:switch wire:model="notify_tier_upgrade" />
                        <div class="flex-1">
                            <div
                                class="text-sm font-medium text-zinc-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                Tier Upgrades
                            </div>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                Notify me when I reach a new loyalty tier
                            </div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer group">
                        <flux:switch wire:model="notify_points_expiring" />
                        <div class="flex-1">
                            <div
                                class="text-sm font-medium text-zinc-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                Points Expiring
                            </div>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                Remind me when my points are about to expire
                            </div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer group">
                        <flux:switch wire:model="notify_referral_success" />
                        <div class="flex-1">
                            <div
                                class="text-sm font-medium text-zinc-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                Referral Success
                            </div>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                Alert me when someone uses my referral code
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Marketing Communications -->
            <div class="px-6 py-6 space-y-4">
                <div>
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Marketing Communications</h3>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Receive updates about promotions and offers
                    </p>
                </div>

                <div class="space-y-4 mt-4">
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <flux:switch wire:model="marketing_emails" />
                        <div class="flex-1">
                            <div
                                class="text-sm font-medium text-zinc-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                Marketing Emails
                            </div>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                Receive emails about special offers and promotions
                            </div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer group">
                        <flux:switch wire:model="newsletter" />
                        <div class="flex-1">
                            <div
                                class="text-sm font-medium text-zinc-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                Newsletter
                            </div>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                Get our monthly newsletter with tips and updates
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-950">
                <div class="flex items-center justify-end gap-3">
                    <flux:button variant="ghost" href="{{ route('portal.dashboard') }}" wire:navigate>
                        Cancel
                    </flux:button>

                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">Save Preferences</span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Saving...
                        </span>
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</div>
