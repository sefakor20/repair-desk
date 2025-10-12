<div>
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">Users</flux:heading>

        @can('create', App\Models\User::class)
            <flux:button href="{{ route('users.create') }}" wire:navigate icon="plus">
                Add User
            </flux:button>
        @endcan
    </div>

    @if (session('success'))
        <flux:callout variant="success" icon="check-circle" class="mb-6">
            {{ session('success') }}
        </flux:callout>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search users..." icon="magnifying-glass" />
        </div>

        <div class="flex gap-3">
            <flux:select wire:model.live="roleFilter" placeholder="All Roles" class="w-40">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="manager">Manager</option>
                <option value="technician">Technician</option>
                <option value="front_desk">Front Desk</option>
            </flux:select>

            <flux:select wire:model.live="statusFilter" placeholder="All Status" class="w-40">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </flux:select>
        </div>
    </div>

    <!-- Desktop Table View (hidden on mobile) -->
    <div class="hidden overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 lg:block">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Name
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Email
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Phone
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Role
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Status
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                @forelse ($users as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $user->name }}
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->email }}
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->phone ?? '—' }}
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <flux:badge
                                :color="match($user->role->value) {
                                                                                                'admin' => 'red',
                                                                                                'manager' => 'blue',
                                                                                                'technician' => 'green',
                                                                                                default => 'gray'
                                                                                            }">
                                {{ $user->role->label() }}
                            </flux:badge>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            @can('update', $user)
                                <button wire:click="toggleStatus('{{ $user->id }}')" wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1 text-sm font-medium">
                                    @if ($user->active)
                                        <flux:badge color="green" icon="check-circle">Active</flux:badge>
                                    @else
                                        <flux:badge color="gray" icon="x-circle">Inactive</flux:badge>
                                    @endif
                                </button>
                            @else
                                @if ($user->active)
                                    <flux:badge color="green" icon="check-circle">Active</flux:badge>
                                @else
                                    <flux:badge color="gray" icon="x-circle">Inactive</flux:badge>
                                @endif
                            @endcan
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                @can('update', $user)
                                    <flux:button href="{{ route('users.edit', $user) }}" wire:navigate size="sm"
                                        variant="ghost" icon="pencil">
                                        Edit
                                    </flux:button>
                                @endcan

                                @can('delete', $user)
                                    <flux:button wire:click="deleteUser('{{ $user->id }}')"
                                        wire:confirm="Are you sure you want to delete this user?" size="sm"
                                        variant="ghost" icon="trash"
                                        class="text-red-600 hover:text-red-700 dark:text-red-400">
                                        Delete
                                    </flux:button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                <flux:icon.users class="mb-3 size-12" />
                                <p class="text-sm font-medium">No users found</p>
                                <p class="mt-1 text-xs">Try adjusting your search or filters</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($users->hasPages())
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    @endif

    <!-- Mobile Card View (visible on mobile) -->
    <div class="space-y-4 lg:hidden">
        @forelse ($users as $user)
            <div
                class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-900">
                <!-- User Header -->
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ $user->name }}
                            </div>
                            <div class="mt-2 flex items-center gap-2">
                                <flux:badge
                                    :color="match($user->role->value) {
                                                                            'admin' => 'red',
                                                                            'manager' => 'blue',
                                                                            'technician' => 'green',
                                                                            default => 'gray'
                                                                        }">
                                    {{ $user->role->label() }}
                                </flux:badge>
                                @can('update', $user)
                                    <button wire:click="toggleStatus('{{ $user->id }}')" wire:loading.attr="disabled"
                                        class="inline-flex items-center gap-1 text-sm font-medium">
                                        @if ($user->active)
                                            <flux:badge color="green" icon="check-circle">Active</flux:badge>
                                        @else
                                            <flux:badge color="gray" icon="x-circle">Inactive</flux:badge>
                                        @endif
                                    </button>
                                @else
                                    @if ($user->active)
                                        <flux:badge color="green" icon="check-circle">Active</flux:badge>
                                    @else
                                        <flux:badge color="gray" icon="x-circle">Inactive</flux:badge>
                                    @endif
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Details -->
                <div class="px-4 py-3">
                    <dl class="space-y-2.5">
                        <div class="flex items-center justify-between text-sm">
                            <dt class="font-medium text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $user->email }}</dd>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <dt class="font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $user->phone ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Actions -->
                <div class="border-t border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-end gap-3">
                        @can('update', $user)
                            <flux:button href="{{ route('users.edit', $user) }}" wire:navigate size="sm"
                                variant="ghost" icon="pencil">
                                Edit
                            </flux:button>
                        @endcan

                        @can('delete', $user)
                            <flux:button wire:click="deleteUser('{{ $user->id }}')"
                                wire:confirm="Are you sure you want to delete this user?" size="sm" variant="ghost"
                                icon="trash" class="text-red-600 hover:text-red-700 dark:text-red-400">
                                Delete
                            </flux:button>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 text-center dark:border-gray-700 dark:bg-gray-900">
                <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                    <flux:icon.users class="mb-3 size-12" />
                    <p class="text-sm font-medium">No users found</p>
                    <p class="mt-1 text-xs">Try adjusting your search or filters</p>
                </div>
            </div>
        @endforelse

        @if ($users->hasPages())
            <div class="mt-6">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
