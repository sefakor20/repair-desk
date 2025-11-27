<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <x-app-logo />
        </a>

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Overview')" class="grid">
                <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>

                <flux:navlist.item icon="chart-pie" :href="route('analytics.dashboard')"
                    :current="request()->routeIs('analytics.*')" wire:navigate>{{ __('Analytics') }}
                </flux:navlist.item>

                @hasAnyStaffPermission(['view_reports', 'manage_settings'])
                    <flux:navlist.item icon="chart-bar" :href="route('reports.index')"
                        :current="request()->routeIs('reports.*')" wire:navigate>{{ __('Reports') }}</flux:navlist.item>
                @endhasAnyStaffPermission
            </flux:navlist.group>

            <flux:navlist.group :heading="__('Operations')" class="grid">
                @hasAnyStaffPermission(['manage_customers', 'create_tickets', 'view_assigned_tickets'])
                    <flux:navlist.item icon="user-group" :href="route('customers.index')"
                        :current="request()->routeIs('customers.*')" wire:navigate>{{ __('Customers') }}</flux:navlist.item>
                @endhasAnyStaffPermission

                <flux:navlist.item icon="device-phone-mobile" :href="route('devices.index')"
                    :current="request()->routeIs('devices.*')" wire:navigate>{{ __('Devices') }}</flux:navlist.item>

                @hasAnyStaffPermission(['manage_tickets', 'view_assigned_tickets', 'create_tickets'])
                    <flux:navlist.item icon="wrench-screwdriver" :href="route('tickets.index')"
                        :current="request()->routeIs('tickets.*')" wire:navigate>{{ __('Tickets') }}</flux:navlist.item>
                @endhasAnyStaffPermission

                @hasAnyStaffPermission(['manage_inventory', 'view_inventory', 'use_inventory'])
                    <flux:navlist.item icon="cube" :href="route('inventory.index')"
                        :current="request()->routeIs('inventory.*')" wire:navigate>{{ __('Inventory') }}
                    </flux:navlist.item>
                @endhasAnyStaffPermission
            </flux:navlist.group>

            <flux:navlist.group :heading="__('Sales & Payments')" class="grid">
                @hasAnyStaffPermission(['create_invoices', 'view_sales', 'process_payments'])
                    <flux:navlist.item icon="document-text" :href="route('invoices.index')"
                        :current="request()->routeIs('invoices.*')" wire:navigate>{{ __('Invoices') }}</flux:navlist.item>
                @endhasAnyStaffPermission

                @hasAnyStaffPermission(['create_sales', 'view_sales'])
                    <flux:navlist.item icon="shopping-cart" :href="route('pos.index')"
                        :current="request()->routeIs('pos.*') && !request()->routeIs('pos.returns.*')" wire:navigate>
                        {{ __('POS') }}</flux:navlist.item>
                @endhasAnyStaffPermission

                @hasAnyStaffPermission(['create_sales', 'view_sales'])
                    <flux:navlist.item icon="arrow-path" :href="route('pos.returns.index')"
                        :current="request()->routeIs('pos.returns.*')" wire:navigate>{{ __('Returns') }}
                    </flux:navlist.item>
                @endhasAnyStaffPermission

                @hasAnyStaffPermission(['manage_cash_drawer', 'process_payments'])
                    <flux:navlist.item icon="banknotes" :href="route('cash-drawer.index')"
                        :current="request()->routeIs('cash-drawer.*')" wire:navigate>{{ __('Cash Drawer') }}
                    </flux:navlist.item>
                @endhasAnyStaffPermission

                <flux:navlist.item icon="clock" :href="route('shifts.index')"
                    :current="request()->routeIs('shifts.*')" wire:navigate>{{ __('Shifts') }}
                </flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group :heading="__('Management')" class="grid">
                @can('viewAny', App\Models\Branch::class)
                    <flux:navlist.item icon="store" :href="route('branches.index')"
                        :current="request()->routeIs('branches.*')" wire:navigate>{{ __('Branches') }}</flux:navlist.item>
                @endcan

                @can('viewAny', App\Models\User::class)
                    <flux:navlist.item icon="users" :href="route('users.index')"
                        :current="request()->routeIs('users.*')" wire:navigate>{{ __('Users') }}</flux:navlist.item>
                @endcan

                @hasAnyStaffPermission(['manage_settings', 'view_reports'])
                    <flux:navlist.item icon="chat-bubble-left-right" :href="route('admin.sms-monitoring')"
                        :current="request()->routeIs('admin.sms-monitoring')" wire:navigate>{{ __('SMS Monitoring') }}
                    </flux:navlist.item>
                @endhasAnyStaffPermission
            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />

        <flux:navlist variant="outline">
            <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit"
                target="_blank">
                {{ __('Repository') }}
            </flux:navlist.item>

            <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire"
                target="_blank">
                {{ __('Documentation') }}
            </flux:navlist.item>
        </flux:navlist>

        <!-- Desktop User Menu -->
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                icon-trailing="chevrons-up-down" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    {{-- Toast Notifications --}}
    <livewire:toast-manager />

    {{-- Command Palette --}}
    <livewire:command-palette />

    {{-- Keyboard Shortcuts Help --}}
    <livewire:keyboard-shortcuts-help />

    {{-- Keyboard Shortcut Indicator (bottom-right corner) --}}
    <div class="fixed bottom-4 right-4 z-40 hidden md:block">
        <button onclick="window.dispatchEvent(new CustomEvent('toggle-shortcuts-help', {detail: {isOpen: true}}))"
            type="button"
            class="group flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs font-medium text-zinc-600 shadow-lg transition-all hover:border-zinc-300 hover:shadow-xl dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-600">
            <svg class="h-3.5 w-3.5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Press</span>
            <kbd
                class="rounded bg-zinc-100 px-1.5 py-0.5 font-mono text-xs font-semibold text-zinc-700 shadow-sm dark:bg-zinc-700 dark:text-zinc-300">?</kbd>
            <span>for shortcuts</span>
        </button>
    </div>

    @fluxScripts
</body>

</html>
