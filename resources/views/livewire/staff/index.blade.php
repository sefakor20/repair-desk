<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Staff Management</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Manage branch staff members and their roles</p>
        </div>
        <button wire:click="openCreateModal"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 dark:hover:bg-blue-500">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Staff Member
        </button>
    </div>

    <!-- Filters -->
    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="grid gap-4 md:grid-cols-5">
            <flux:field>
                <flux:input wire:model.live="search" placeholder="Search by name or email..." />
            </flux:field>
            <flux:field>
                <flux:select wire:model.live="roleFilter" placeholder="All Roles">
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}">{{ $role->label() }}</option>
                    @endforeach
                </flux:select>
            </flux:field>
            <flux:field>
                <flux:select wire:model.live="statusFilter" placeholder="All Status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </flux:select>
            </flux:field>
            @if (auth()->user()->isSuperAdmin())
                <flux:field>
                    <flux:select wire:model.live="branchFilter" placeholder="All Branches">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>
            @endif
            <div class="flex items-end">
                <button wire:click="clearFilters"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                    Clear Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Staff List -->
    @if ($staff->count() > 0)
        <div class="overflow-x-auto rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
            <table class="w-full">
                <thead class="border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Name</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Email</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Branch</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Role</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Hire Date</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Status</th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($staff as $member)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                            <td class="px-6 py-4 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $member->user->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                {{ $member->user->email }}
                            </td>
                            <td class="px-6 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                {{ $member->branch->name }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <flux:select wire:change="updateRole({{ $member->id }}, $event.target.value)"
                                    wire:model="member.role" class="w-full">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->value }}">{{ $role->label() }}</option>
                                    @endforeach
                                </flux:select>
                            </td>
                            <td class="px-6 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                {{ $member->hire_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $member->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300' }}">
                                    {{ $member->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="toggleActive({{ $member->id }})"
                                        class="inline-flex items-center gap-1 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-xs font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                                        {{ $member->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    <button wire:click="delete({{ $member->id }})" wire:confirm="Are you sure?"
                                        class="inline-flex items-center gap-1 rounded-lg border border-red-300 bg-white px-3 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-600 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center">
            {{ $staff->links() }}
        </div>
    @else
        <div
            class="rounded-lg border border-zinc-200 bg-zinc-50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-800">
            <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">No staff members found</h3>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Get started by adding your first staff member</p>
        </div>
    @endif

    <!-- Create Modal -->
    @if ($showCreateModal)
        <flux:modal open="true" wire:close="closeCreateModal">
            <flux:heading>Add Staff Member</flux:heading>

            <form wire:submit.prevent="save" class="space-y-4">
                <flux:field label="User" required>
                    <flux:select wire:model="form.user_id" required>
                        <option value="">Select a user</option>
                        @foreach ($availableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </flux:select>
                    @error('form.user_id')
                        <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                    @enderror
                </flux:field>

                <flux:field label="Role" required>
                    <flux:select wire:model="form.role" required>
                        <option value="">Select a role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}">{{ $role->label() }} - {{ $role->description() }}
                            </option>
                        @endforeach
                    </flux:select>
                    @error('form.role')
                        <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                    @enderror
                </flux:field>

                <flux:field label="Hire Date" required>
                    <flux:input type="date" wire:model="form.hire_date" required />
                    @error('form.hire_date')
                        <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                    @enderror
                </flux:field>

                <flux:field label="Notes">
                    <flux:textarea wire:model="form.notes" placeholder="Add any notes about this staff member..." />
                    @error('form.notes')
                        <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                    @enderror
                </flux:field>

                <div class="flex justify-end gap-2 pt-4">
                    <flux:button variant="ghost" wire:click="closeCreateModal">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Add Staff Member</flux:button>
                </div>
            </form>
        </flux:modal>
    @endif
</div>
