<div>
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <flux:heading size="lg">Contacts</flux:heading>
                <flux:text variant="muted">Manage individual contacts for SMS campaigns</flux:text>
            </div>
            @can('create', App\Models\Contact::class)
                <flux:button :href="route('admin.contacts.create')" variant="primary" icon="plus">
                    Add Contact
                </flux:button>
            @endcan
        </div>
    </div>

    <div class="space-y-6">
        <!-- Search -->
        <div class="flex gap-4">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search contacts..." class="flex-1"
                icon="magnifying-glass" />
        </div>

        <!-- Contacts Table -->
        @if ($this->contacts->count())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Name
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Email
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Phone
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Company
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($this->contacts as $contact)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $contact->name }}
                                            </div>
                                            @if ($contact->position)
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $contact->position }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $contact->email ?: '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $contact->phone ?: '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $contact->company ?: '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($contact->is_active)
                                        <flux:badge color="green">Active</flux:badge>
                                    @else
                                        <flux:badge color="red">Inactive</flux:badge>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        @can('update', $contact)
                                            <flux:button :href="route('admin.contacts.edit', $contact)" variant="subtle"
                                                size="sm" icon="pencil">
                                                Edit
                                            </flux:button>
                                        @endcan

                                        @can('delete', $contact)
                                            <flux:button wire:click="delete('{{ $contact->id }}')"
                                                wire:confirm="Are you sure you want to delete this contact?"
                                                variant="danger" size="sm" icon="trash">
                                                Delete
                                            </flux:button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $this->contacts->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No contacts</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new contact.</p>
                @can('create', App\Models\Contact::class)
                    <div class="mt-6">
                        <flux:button :href="route('admin.contacts.create')" variant="primary" icon="plus">
                            Add Contact
                        </flux:button>
                    </div>
                @endcan
            </div>
        @endif
    </div>
</div>
