<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">SMS Templates</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage reusable SMS message templates for automation
                and quick sending</p>
        </div>
        <flux:button icon="plus" variant="primary" wire:click="openCreateModal">
            Create Template
        </flux:button>
    </div>

    <!-- Search Bar -->
    <div class="flex items-center space-x-4">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search templates..."
                icon="magnifying-glass" />
        </div>
    </div>

    <!-- Templates Table -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Template
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Key
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Message Preview
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Variables
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($this->templates as $template)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $template->name }}
                                    </div>
                                    @if ($template->description)
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ Str::limit($template->description, 50) }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <flux:badge variant="gray" size="sm">{{ $template->key }}</flux:badge>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 dark:text-gray-100 max-w-xs truncate">
                                    {{ Str::limit($template->message, 80) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <flux:badge :variant="$template->is_active ? 'success' : 'gray'" size="sm">
                                    {{ $template->is_active ? 'Active' : 'Inactive' }}
                                </flux:badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $variables = $template->extractVariables();
                                @endphp
                                @if (count($variables) > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach (array_slice($variables, 0, 3) as $variable)
                                            <flux:badge variant="gray" size="xs">{{ $variable }}</flux:badge>
                                        @endforeach
                                        @if (count($variables) > 3)
                                            <flux:badge variant="gray" size="xs">+{{ count($variables) - 3 }} more
                                            </flux:badge>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">No variables</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <flux:button size="sm" variant="ghost" icon="eye"
                                    wire:click="previewTemplate({{ $template->id }})">
                                    Preview
                                </flux:button>
                                <flux:button size="sm" variant="ghost" icon="pencil"
                                    wire:click="openEditModal({{ $template->id }})">
                                    Edit
                                </flux:button>
                                <flux:button size="sm" :variant="$template->is_active ? 'ghost' : 'success'"
                                    :icon="$template->is_active ? 'x-mark' : 'check'"
                                    wire:click="toggleStatus({{ $template->id }})">
                                    {{ $template->is_active ? 'Deactivate' : 'Activate' }}
                                </flux:button>
                                <flux:button size="sm" variant="danger" icon="trash"
                                    wire:click="deleteTemplate({{ $template->id }})"
                                    wire:confirm="Are you sure you want to delete this template?">
                                    Delete
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                        viewBox="0 0 48 48">
                                        <path
                                            d="M8 14v20c0 4.418 7.163 8 16 8 1.381 0 2.721-.087 4-.252M8 14c0 4.418 7.163 8 16 8s16-3.582 16-8M8 14c0-4.418 7.163-8 16-8s16 3.582 16 8m0 0v14m-16-4c1.381 0 2.721-.087 4-.252" />
                                    </svg>
                                    <p class="mt-4 text-lg font-medium">No SMS templates found</p>
                                    <p class="mt-2">Create your first SMS template to get started</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->templates->hasPages())
            <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $this->templates->links() }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Template Modal -->
    <flux:modal name="template-modal" :show="$showCreateModal">
        <flux:modal.header>
            {{ $editingTemplate ? 'Edit Template' : 'Create New Template' }}
        </flux:modal.header>

        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Template Name</flux:label>
                    <flux:input wire:model="name" placeholder="e.g., Appointment Reminder" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Template Key</flux:label>
                    <flux:input wire:model="key" placeholder="e.g., appointment_reminder" />
                    <flux:error name="key" />
                    <flux:description>Unique identifier for programmatic usage</flux:description>
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Message Template</flux:label>
                <flux:textarea wire:model="message"
                    placeholder="Hello {customer_name}, your appointment for {device} is scheduled for {appointment_date}."
                    rows="4" />
                <flux:error name="message" />
                <flux:description>
                    Use {variable_name} for dynamic content. Available variables: customer_name, customer_phone,
                    ticket_number, device, status, branch_name, current_date, current_time
                </flux:description>
            </flux:field>

            <flux:field>
                <flux:label>Description (Optional)</flux:label>
                <flux:textarea wire:model="description" placeholder="Brief description of when this template is used"
                    rows="2" />
                <flux:error name="description" />
            </flux:field>

            <flux:field>
                <flux:checkbox wire:model="is_active">Active</flux:checkbox>
                <flux:description>Only active templates can be used for sending messages</flux:description>
            </flux:field>
        </div>

        <flux:modal.footer>
            <flux:button variant="ghost" wire:click="resetForm">Cancel</flux:button>
            <flux:button variant="primary" wire:click="save">
                {{ $editingTemplate ? 'Update Template' : 'Create Template' }}
            </flux:button>
        </flux:modal.footer>
    </flux:modal>

    <!-- Scripts for handling events -->
    <script>
        document.addEventListener('livewire:init', function() {
            @this.on('template-created', function() {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: 'Template created successfully!'
                    }
                }));
            });

            @this.on('template-updated', function() {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: 'Template updated successfully!'
                    }
                }));
            });

            @this.on('template-deleted', function() {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: 'Template deleted successfully!'
                    }
                }));
            });

            @this.on('template-status-changed', function() {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: 'Template status updated!'
                    }
                }));
            });

            @this.on('error', function(event) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'error',
                        message: event.detail
                    }
                }));
            });

            @this.on('show-preview', function(event) {
                alert('Preview:\n\n' + event.detail.message + '\n\nVariables used: ' + Object.keys(event
                    .detail.variables).join(', '));
            });
        });
    </script>
</div>
