<div class="space-y-6 relative">
    {{-- Loading Overlay --}}
    <x-loading-overlay wire:loading wire:target="search, delete, toggleStatus" />
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Branches</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Manage your business locations and branches</p>
        </div>
        <a href="{{ route('branches.create') }}" wire:navigate
            class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-zinc-800 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 dark:focus:ring-white">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Branch
        </a>
    </div>

    <!-- Search -->
    <div class="relative">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <svg wire:loading.remove wire:target="search" class="h-5 w-5 text-zinc-400" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <svg wire:loading wire:target="search" class="h-5 w-5 animate-spin text-zinc-400" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        </div>
        <input type="search" wire:model.live.debounce.300ms="search"
            placeholder="Search branches by name, code, or city..."
            class="block w-full rounded-lg border border-zinc-200 bg-white py-2.5 pl-10 pr-3 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500 dark:focus:border-white dark:focus:ring-white">
    </div>

    <!-- Desktop Table View (hidden on mobile) -->
    <div
        class="hidden overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800 lg:block">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-900">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Branch</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Code</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            City</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Status</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Main</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Users</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Tickets</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Inventory</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            POS Sales</th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                    @forelse ($branches as $branch)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium text-zinc-900 dark:text-white">
                                <a href="{{ route('branches.show', $branch) }}"
                                    class="hover:underline">{{ $branch->name }}</a>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-zinc-700 dark:text-zinc-300">{{ $branch->code }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-zinc-700 dark:text-zinc-300">{{ $branch->city }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $branch->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                    {{ $branch->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $branch->is_main ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300' }}">
                                    {{ $branch->is_main ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-zinc-700 dark:text-zinc-300">
                                {{ $branch->users_count }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-zinc-700 dark:text-zinc-300">
                                {{ $branch->tickets_count }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-zinc-700 dark:text-zinc-300">
                                {{ $branch->inventory_items_count }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-zinc-700 dark:text-zinc-300">
                                {{ $branch->pos_sales_count }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('branches.show', $branch) }}" wire:navigate
                                        class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white"
                                        title="View">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('branches.edit', $branch) }}" wire:navigate
                                        class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white"
                                        title="Edit">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <button wire:click="delete('{{ $branch->id }}')"
                                        wire:confirm="Are you sure you want to delete this branch?"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                        title="Delete">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    <button wire:click="toggleStatus('{{ $branch->id }}')"
                                        class="text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white"
                                        title="{{ $branch->is_active ? 'Deactivate' : 'Activate' }}">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center">
                                @if ($search)
                                    <x-empty-state icon="search" title="{{ __('No branches found') }}"
                                        description="{{ __('Try adjusting your search criteria') }}" />
                                @else
                                    <x-empty-state icon="store" title="{{ __('No branches found') }}"
                                        description="{{ __('Get started by creating a new branch') }}"
                                        :action-route="route('branches.create')" action-label="{{ __('New Branch') }}" />
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($branches->hasPages())
            <div class="border-t border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                {{ $branches->links() }}
            </div>
        @endif
    </div>

    <!-- Mobile Card View (visible on mobile) -->
    <div class="space-y-4 lg:hidden">
        @forelse ($branches as $branch)
            <div
                class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm transition-shadow hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800">
                <!-- Branch Header -->
                <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-200 text-sm font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                                {{ strtoupper(substr($branch->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ $branch->name }}
                                </div>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <span
                                        class="inline-flex items-center rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                                        {{ $branch->code }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Branch Details -->
                <div class="px-4 py-3">
                    <dl class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <dt class="font-medium text-zinc-500 dark:text-zinc-400">City</dt>
                            <dd class="text-zinc-900 dark:text-white">{{ $branch->city }}</dd>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <dt class="font-medium text-zinc-500 dark:text-zinc-400">Status</dt>
                            <dd>
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $branch->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                    {{ $branch->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <dt class="font-medium text-zinc-500 dark:text-zinc-400">Main</dt>
                            <dd>
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $branch->is_main ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300' }}">
                                    {{ $branch->is_main ? 'Yes' : 'No' }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <dt class="font-medium text-zinc-500 dark:text-zinc-400">Users</dt>
                            <dd class="text-zinc-900 dark:text-white">{{ $branch->users_count }}</dd>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <dt class="font-medium text-zinc-500 dark:text-zinc-400">Tickets</dt>
                            <dd class="text-zinc-900 dark:text-white">{{ $branch->tickets_count }}</dd>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <dt class="font-medium text-zinc-500 dark:text-zinc-400">Inventory</dt>
                            <dd class="text-zinc-900 dark:text-white">{{ $branch->inventory_items_count }}</dd>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <dt class="font-medium text-zinc-500 dark:text-zinc-400">POS Sales</dt>
                            <dd class="text-zinc-900 dark:text-white">{{ $branch->pos_sales_count }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Actions -->
                <div class="border-t border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('branches.show', $branch) }}" wire:navigate
                            class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            View
                        </a>
                        <a href="{{ route('branches.edit', $branch) }}" wire:navigate
                            class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit
                        </a>
                        <button wire:click="delete('{{ $branch->id }}')"
                            wire:confirm="Are you sure you want to delete this branch?"
                            class="inline-flex items-center gap-2 rounded-lg border border-red-300 bg-white px-3 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-600 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>
                        <button wire:click="toggleStatus('{{ $branch->id }}')"
                            class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                            title="{{ $branch->is_active ? 'Deactivate' : 'Activate' }}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $branch->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </div>
                </div>
            </div>
        @empty
            @if ($search)
                <x-empty-state icon="search" title="{{ __('No branches found') }}"
                    description="{{ __('Try adjusting your search criteria') }}" />
            @else
                <x-empty-state icon="store" title="{{ __('No branches found') }}"
                    description="{{ __('Get started by creating a new branch') }}" :action-route="route('branches.create')"
                    action-label="{{ __('New Branch') }}" />
            @endif
        @endforelse

        @if ($branches->hasPages())
            <div class="rounded-lg border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                {{ $branches->links() }}
            </div>
        @endif
    </div>
</div>
