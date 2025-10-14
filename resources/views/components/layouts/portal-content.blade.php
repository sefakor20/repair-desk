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
            <div class="flex gap-8">
                <a href="{{ route('portal.loyalty.dashboard', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}"
                    class="{{ request()->routeIs('portal.loyalty.dashboard') ? 'border-purple-500 text-purple-600 dark:border-purple-400 dark:text-purple-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }} -mb-px flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-all duration-200 hover:scale-105 active:scale-95">
                    <flux:icon.home class="size-5 transition-transform duration-200 group-hover:scale-110" />
                    Dashboard
                </a>
                <a href="{{ route('portal.tickets.index', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}"
                    class="{{ request()->routeIs('portal.tickets.*') ? 'border-purple-500 text-purple-600 dark:border-purple-400 dark:text-purple-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }} -mb-px flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-all duration-200 hover:scale-105 active:scale-95">
                    <flux:icon.wrench class="size-5 transition-transform duration-200 group-hover:scale-110" />
                    My Repairs
                </a>
                <a href="{{ route('portal.loyalty.rewards', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}"
                    class="{{ request()->routeIs('portal.loyalty.rewards') ? 'border-purple-500 text-purple-600 dark:border-purple-400 dark:text-purple-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }} -mb-px flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-all duration-200 hover:scale-105 active:scale-95">
                    <flux:icon.gift class="size-5 transition-transform duration-200 group-hover:scale-110" />
                    Rewards
                </a>
                <a href="{{ route('portal.loyalty.history', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}"
                    class="{{ request()->routeIs('portal.loyalty.history') ? 'border-purple-500 text-purple-600 dark:border-purple-400 dark:text-purple-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }} -mb-px flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-all duration-200 hover:scale-105 active:scale-95">
                    <flux:icon.clock class="size-5 transition-transform duration-200 group-hover:scale-110" />
                    History
                </a>
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
