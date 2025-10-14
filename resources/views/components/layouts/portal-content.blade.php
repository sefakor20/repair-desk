{{-- Portal Layout Content (for use within Livewire components) --}}
<div class="flex min-h-screen flex-col">
    {{-- Top Navigation --}}
    <header class="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                {{-- Logo/Brand --}}
                <div class="flex items-center gap-3">
                    <flux:brand
                        href="{{ route('portal.loyalty.dashboard', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}"
                        class="max-w-32" />
                    <flux:separator vertical class="h-8" />
                    <flux:heading size="lg">Loyalty Portal</flux:heading>
                </div>

                {{-- Points Balance --}}
                @if (isset($customer) && $customer->loyaltyAccount)
                    <div
                        class="flex items-center gap-4 rounded-lg bg-gradient-to-r from-purple-500 to-purple-600 px-4 py-2 shadow-lg shadow-purple-500/50 transition-all duration-300 hover:scale-105 hover:shadow-xl hover:shadow-purple-500/50">
                        <div class="text-right">
                            <div class="text-xs font-medium text-white/90">Your Points</div>
                            <div class="text-2xl font-bold text-white animate-pulse">
                                {{ number_format($customer->loyaltyAccount->total_points) }}
                            </div>
                        </div>
                        <flux:icon.gift class="size-6 text-white animate-bounce" style="animation-duration: 2s;" />
                    </div>
                @endif
            </div>
        </div>
    </header>

    {{-- Navigation Tabs --}}
    <nav class="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex gap-8 overflow-x-auto">
                <a href="{{ route('portal.loyalty.dashboard', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}"
                    class="{{ request()->routeIs('portal.loyalty.dashboard') ? 'border-purple-500 text-purple-600 dark:border-purple-400 dark:text-purple-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }} -mb-px flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-all duration-200 hover:scale-105 active:scale-95 whitespace-nowrap">
                    <flux:icon.home class="size-5 transition-transform duration-200 group-hover:scale-110" />
                    Dashboard
                </a>
                <a href="{{ route('portal.tickets.index', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}"
                    class="{{ request()->routeIs('portal.tickets.*') ? 'border-purple-500 text-purple-600 dark:border-purple-400 dark:text-purple-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }} -mb-px flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-all duration-200 hover:scale-105 active:scale-95 whitespace-nowrap">
                    <flux:icon.wrench class="size-5 transition-transform duration-200 group-hover:scale-110" />
                    My Repairs
                </a>
                <a href="{{ route('portal.loyalty.rewards', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}"
                    class="{{ request()->routeIs('portal.loyalty.rewards') ? 'border-purple-500 text-purple-600 dark:border-purple-400 dark:text-purple-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }} -mb-px flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-all duration-200 hover:scale-105 active:scale-95 whitespace-nowrap">
                    <flux:icon.gift class="size-5 transition-transform duration-200 group-hover:scale-110" />
                    Rewards
                </a>
                <a href="{{ route('portal.referrals.index', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}"
                    class="{{ request()->routeIs('portal.referrals.*') ? 'border-purple-500 text-purple-600 dark:border-purple-400 dark:text-purple-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }} -mb-px flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-all duration-200 hover:scale-105 active:scale-95 whitespace-nowrap">
                    <svg class="size-5 transition-transform duration-200 group-hover:scale-110" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Referrals
                </a>
                <a href="{{ route('portal.profile.transfer-points', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}"
                    class="{{ request()->routeIs('portal.profile.transfer-points') ? 'border-purple-500 text-purple-600 dark:border-purple-400 dark:text-purple-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }} -mb-px flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-all duration-200 hover:scale-105 active:scale-95 whitespace-nowrap">
                    <svg class="size-5 transition-transform duration-200 group-hover:scale-110" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Transfer
                </a>
                <a href="{{ route('portal.loyalty.history', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}"
                    class="{{ request()->routeIs('portal.loyalty.history') ? 'border-purple-500 text-purple-600 dark:border-purple-400 dark:text-purple-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }} -mb-px flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-all duration-200 hover:scale-105 active:scale-95 whitespace-nowrap">
                    <flux:icon.clock class="size-5 transition-transform duration-200 group-hover:scale-110" />
                    History
                </a>

                {{-- Profile Dropdown --}}
                <div class="-mb-px ml-auto">
                    <flux:dropdown position="bottom-end" class="flex items-center">
                        <flux:button variant="ghost"
                            class="!px-3 !py-2 h-full rounded-none hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all duration-200">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </flux:button>

                        <flux:menu>
                            <flux:menu.item
                                href="{{ route('portal.profile.edit', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}">
                                <flux:icon.user class="size-4" />
                                Edit Profile
                            </flux:menu.item>
                            <flux:menu.item
                                href="{{ route('portal.settings.preferences', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}">
                                <flux:icon.cog class="size-4" />
                                Preferences
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="flex-1 py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{ $slot }}
        </div>
    </main>

    {{-- Footer --}}
    <footer class="border-t border-zinc-200 bg-white py-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <flux:text class="text-sm text-zinc-500">
                    © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </flux:text>
                <flux:text class="text-sm text-zinc-500">
                    Questions? Contact us at
                    <a href="mailto:support@example.com"
                        class="text-purple-600 transition-colors duration-200 hover:text-purple-700 dark:text-purple-400">
                        support@example.com
                    </a>
                </flux:text>
            </div>
        </div>
    </footer>
</div>

{{-- Global Loading Indicator --}}
<div wire:loading class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 backdrop-blur-sm">
    <div class="rounded-lg bg-white p-6 shadow-2xl dark:bg-zinc-800">
        <div class="flex items-center gap-3">
            <svg class="h-8 w-8 animate-spin text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <flux:text class="font-medium">Loading...</flux:text>
        </div>
    </div>
</div>

{{-- Toast Notifications --}}
<livewire:toast-manager />
