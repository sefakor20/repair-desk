<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">SMS Automation Triggers</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Configure automated SMS messages for ticket workflow
                events</p>
        </div>
        <flux:button icon="plus" variant="primary" wire:click="openCreateModal">
            Create Trigger
        </flux:button>
    </div>

    <!-- Search Bar -->
    <div class="flex items-center space-x-4">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search triggers..."
                icon="magnifying-glass" />
        </div>
    </div>

    <!-- Triggers Table -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Trigger
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Event
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Template
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Delay
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Recipients
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($this->triggers as $trigger)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $trigger->name }}
                                    </div>
                                    @if ($trigger->description)
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ Str::limit($trigger->description, 50) }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <flux:badge variant="gray" size="sm">
                                    {{ $this->availableTriggerEvents[$trigger->trigger_event] ?? $trigger->trigger_event }}
                                </flux:badge>
                                @if ($trigger->trigger_conditions)
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ count($trigger->trigger_conditions) }} condition(s)
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                    {{ $trigger->smsTemplate?->name ?? 'Template not found' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                    @if ($trigger->delay_minutes === 0)
                                        Immediately
                                    @elseif($trigger->delay_minutes < 60)
                                        {{ $trigger->delay_minutes }} min
                                    @else
                                        {{ intval($trigger->delay_minutes / 60) }}h
                                        @if ($trigger->delay_minutes % 60 > 0)
                                            {{ $trigger->delay_minutes % 60 }}m
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @if ($trigger->send_to_customer)
                                        <flux:badge variant="blue" size="xs">Customer</flux:badge>
                                    @endif
                                    @if ($trigger->send_to_staff)
                                        <flux:badge variant="green" size="xs">Staff</flux:badge>
                                    @endif
                                    @if (count($trigger->additional_recipients ?? []) > 0)
                                        <flux:badge variant="gray" size="xs">
                                            +{{ count($trigger->additional_recipients) }}</flux:badge>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <flux:badge :variant="$trigger->is_active ? 'success' : 'gray'" size="sm">
                                    {{ $trigger->is_active ? 'Active' : 'Inactive' }}
                                </flux:badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <flux:button size="sm" variant="ghost" icon="pencil"
                                    wire:click="openEditModal({{ $trigger->id }})">
                                    Edit
                                </flux:button>
                                <flux:button size="sm" :variant="$trigger->is_active ? 'ghost' : 'success'"
                                    :icon="$trigger->is_active ? 'x-mark' : 'check'"
                                    wire:click="toggleStatus({{ $trigger->id }})">
                                    {{ $trigger->is_active ? 'Deactivate' : 'Activate' }}
                                </flux:button>
                                <flux:button size="sm" variant="danger" icon="trash"
                                    wire:click="deleteTrigger({{ $trigger->id }})"
                                    wire:confirm="Are you sure you want to delete this automation trigger?">
                                    Delete
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                        viewBox="0 0 48 48">
                                        <path
                                            d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.713-3.714M14 40v-4c0-1.313.253-2.566.713-3.714m0 0A9.971 9.971 0 0124 24c4.004 0 7.625 2.225 9.287 5.286" />
                                    </svg>
                                    <p class="mt-4 text-lg font-medium">No automation triggers found</p>
                                    <p class="mt-2">Create your first automation trigger to start automating SMS
                                        notifications</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->triggers->hasPages())
            <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $this->triggers->links() }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Trigger Modal -->
    <flux:modal name="trigger-modal" :show="$showCreateModal" class="w-full max-w-4xl">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <flux:heading>{{ $editingTrigger ? 'Edit Automation Trigger' : 'Create New Automation Trigger' }}
            </flux:heading>
        </div>

        <!-- Modal Body -->
        <div class="px-6 py-4 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Trigger Name</flux:label>
                    <flux:input wire:model="name" placeholder="e.g., Repair Completion Notification" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Trigger Event</flux:label>
                    <flux:select wire:model="trigger_event" placeholder="Select an event">
                        @foreach ($this->availableTriggerEvents as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="trigger_event" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Description (Optional)</flux:label>
                <flux:textarea wire:model="description" placeholder="Brief description of this automation trigger"
                    rows="2" />
                <flux:error name="description" />
            </flux:field>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>SMS Template</flux:label>
                    <flux:select wire:model="sms_template_id" placeholder="Select a template">
                        @foreach ($this->availableTemplates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="sms_template_id" />
                    <flux:description>Only active templates are shown</flux:description>
                </flux:field>

                <flux:field>
                    <flux:label>Delay (minutes)</flux:label>
                    <flux:input type="number" wire:model="delay_minutes" placeholder="0" min="0" />
                    <flux:error name="delay_minutes" />
                    <flux:description>0 = send immediately, 60 = send after 1 hour</flux:description>
                </flux:field>
            </div>

            <!-- Trigger Conditions -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Trigger Conditions</h3>
                    <flux:badge variant="gray" size="sm">Optional</flux:badge>
                </div>

                <div class="space-y-3">
                    @if (count($trigger_conditions) > 0)
                        @foreach ($trigger_conditions as $field => $value)
                            <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
                                <div class="text-sm">
                                    <span class="font-medium">{{ $field }}</span> =
                                    <span class="text-gray-600 dark:text-gray-400">{{ $value }}</span>
                                </div>
                                <flux:button size="sm" variant="danger"
                                    wire:click="removeCondition('{{ $field }}')">
                                    Remove
                                </flux:button>
                            </div>
                        @endforeach
                    @endif

                    <div class="flex gap-3">
                        <flux:input wire:model="newConditionField" placeholder="Field name (e.g., status)"
                            class="flex-1" />
                        <flux:input wire:model="newConditionValue" placeholder="Expected value" class="flex-1" />
                        <flux:button wire:click="addCondition" variant="primary">Add</flux:button>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Add conditions to only trigger when specific criteria are met (e.g., status = "completed")
                    </p>
                </div>
            </div>

            <!-- Recipients -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Recipients</h3>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:checkbox wire:model="send_to_customer">Send to Customer</flux:checkbox>
                            <flux:description>Send SMS to the customer associated with the ticket/appointment
                            </flux:description>
                        </flux:field>

                        <flux:field>
                            <flux:checkbox wire:model="send_to_staff">Send to Assigned Staff</flux:checkbox>
                            <flux:description>Send SMS to staff member assigned to the ticket</flux:description>
                        </flux:field>
                    </div>

                    <!-- Additional Recipients -->
                    <div>
                        <flux:label>Additional Recipients</flux:label>
                        @if (count($additional_recipients) > 0)
                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach ($additional_recipients as $recipient)
                                    <div
                                        class="flex items-center bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm">
                                        {{ $recipient }}
                                        <flux:button size="xs" variant="ghost"
                                            wire:click="removeRecipient('{{ $recipient }}')"
                                            class="ml-2 text-blue-600 hover:text-blue-800">
                                            ×
                                        </flux:button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex gap-3">
                            <flux:input wire:model="newRecipient" placeholder="+1234567890" class="flex-1" />
                            <flux:button wire:click="addRecipient" variant="primary">Add</flux:button>
                        </div>
                        <flux:error name="newRecipient" />
                        <flux:description>Add additional phone numbers to receive this SMS</flux:description>
                    </div>
                </div>
            </div>

            <flux:field>
                <flux:checkbox wire:model="is_active">Active</flux:checkbox>
                <flux:description>Only active triggers will send automated SMS messages</flux:description>
            </flux:field>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
            <flux:button variant="ghost" wire:click="resetForm">Cancel</flux:button>
            <flux:button variant="primary" wire:click="save">
                {{ $editingTrigger ? 'Update Trigger' : 'Create Trigger' }}
            </flux:button>
        </div>
    </flux:modal>

    <!-- Scripts for handling events -->
    <script>
        document.addEventListener('livewire:init', function() {
            @this.on('trigger-created', function() {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: 'Automation trigger created successfully!'
                    }
                }));
            });

            @this.on('trigger-updated', function() {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: 'Automation trigger updated successfully!'
                    }
                }));
            });

            @this.on('trigger-deleted', function() {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: 'Automation trigger deleted successfully!'
                    }
                }));
            });

            @this.on('trigger-status-changed', function() {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: 'Automation trigger status updated!'
                    }
                }));
            });
        });
    </script>
</div>
