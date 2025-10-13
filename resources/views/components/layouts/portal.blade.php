<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' - ' : '' }}{{ config('app.name') }} - Loyalty Portal</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxStyles
</head>

<body class="min-h-screen bg-zinc-50 font-sans antialiased dark:bg-zinc-900">
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
                            class="flex items-center gap-4 rounded-lg bg-gradient-to-r from-purple-500 to-purple-600 px-4 py-2 text-white shadow-sm">
                            <div class="text-right">
                                <flux:text class="text-xs font-medium opacity-90">Your Points</flux:text>
                                <flux:heading size="lg">
                                    {{ number_format($customer->loyaltyAccount->total_points) }}
                                </flux:heading>
                            </div>
                            <flux:icon.gift class="size-6" />
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
                        class="{{ request()->routeIs('portal.loyalty.dashboard') ? 'border-purple-500 text-purple-600 dark:border-purple-400 dark:text-purple-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }} -mb-px flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors">
                        <flux:icon.home class="size-5" />
                        Dashboard
                    </a>
                    <a href="{{ route('portal.tickets.index', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}"
                        class="{{ request()->routeIs('portal.tickets.*') ? 'border-purple-500 text-purple-600 dark:border-purple-400 dark:text-purple-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }} -mb-px flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors">
                        <flux:icon.wrench class="size-5" />
                        My Repairs
                    </a>
                    <a href="{{ route('portal.loyalty.rewards', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}"
                        class="{{ request()->routeIs('portal.loyalty.rewards') ? 'border-purple-500 text-purple-600 dark:border-purple-400 dark:text-purple-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }} -mb-px flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors">
                        <flux:icon.gift class="size-5" />
                        Rewards
                    </a>
                    <a href="{{ route('portal.loyalty.history', ['customer' => $customer->id, 'token' => $customer->portal_access_token]) }}"
                        class="{{ request()->routeIs('portal.loyalty.history') ? 'border-purple-500 text-purple-600 dark:border-purple-400 dark:text-purple-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }} -mb-px flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors">
                        <flux:icon.clock class="size-5" />
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
                            class="text-purple-600 hover:text-purple-700 dark:text-purple-400">
                            support@example.com
                        </a>
                    </flux:text>
                </div>
            </div>
        </footer>
    </div>

    {{-- Toast Notifications --}}
    <livewire:toast-manager />

    @fluxScripts
</body>

</html>
