<div>
    {{-- Header --}}
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">Create SMS Campaign</flux:heading>
        <flux:subheading>Send bulk SMS messages to targeted customer segments</flux:subheading>
    </div>

    <form wire:submit="create">
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Main Form --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Campaign Details --}}
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                    <h3 class="mb-4 text-lg font-medium text-zinc-900 dark:text-zinc-100">Campaign Details</h3>

                    <div class="space-y-4">
                        <div>
                            <flux:input wire:model="name" label="Campaign Name" placeholder="e.g., Monthly Newsletter"
                                required />
                            @error('name')
                                <span class="mt-1 text-xs text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Template Selector --}}
                        <div>
                            <flux:select wire:model.live="selectedTemplate" label="Message Template"
                                wire:change="selectTemplate">
                                @foreach ($this->availableTemplates as $key => $template)
                                    <option value="{{ $key }}">
                                        {{ $key ? ucwords(str_replace('_', ' ', $key)) : $template }}</option>
                                @endforeach
                            </flux:select>
                            @if ($selectedTemplate)
                                <div class="mt-2">
                                    <flux:button variant="ghost" size="sm" wire:click="clearTemplate"
                                        type="button" icon="x-mark">
                                        Clear Template
                                    </flux:button>
                                </div>
                            @endif
                        </div>

                        <div>
                            <flux:textarea wire:model.live.debounce.300ms="message" label="Message"
                                placeholder="Your SMS message..." rows="4" required />
                            <div class="mt-1 flex items-center justify-between text-xs">
                                <span class="text-zinc-500">
                                    {{ strlen($message) }} characters
                                </span>
                                @if ($message)
                                    @php
                                        $segments = $this->getSegmentCount();
                                    @endphp
                                    <span class="text-zinc-500">
                                        ~{{ $segments }} SMS segment{{ $segments !== 1 ? 's' : '' }}
                                    </span>
                                @endif
                            </div>
                            @error('message')
                                <span class="mt-1 text-xs text-red-600">{{ $message }}</span>
                            @enderror

                            {{-- Preview and Test Send Actions --}}
                            @if ($message)
                                <div class="mt-3 flex gap-2">
                                    <flux:button variant="ghost" size="sm" wire:click="showPreviewModal"
                                        type="button" icon="eye">
                                        Preview
                                    </flux:button>
                                    <flux:button variant="ghost" size="sm" wire:click="showTestSendModal"
                                        type="button" icon="paper-airplane">
                                        Send Test
                                    </flux:button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Audience Targeting --}}
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                    <h3 class="mb-4 text-lg font-medium text-zinc-900 dark:text-zinc-100">Target Audience</h3>

                    <div class="space-y-4">
                        <div>
                            <flux:select wire:model.live="segmentType" label="Target Audience">
                                <option value="all">All Customers</option>
                                <option value="recent">Recent Customers</option>
                                <option value="active">Active Customers (with recent tickets)</option>
                                <option value="high_value">High-Value Customers</option>
                                <option value="frequent_customers">Frequent Customers</option>
                                <option value="contacts">Selected Contacts</option>
                            </flux:select>
                            @error('segmentType')
                                <span class="mt-1 text-xs text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        @if ($segmentType === 'recent')
                            <div>
                                <flux:input wire:model.live="recentDays" type="number" label="Created Within (days)"
                                    placeholder="30" min="1" max="365" />
                                @error('recentDays')
                                    <span class="mt-1 text-xs text-red-600">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        @if ($segmentType === 'high_value')
                            <div>
                                <flux:input wire:model.live="minSpent" type="number" step="0.01"
                                    label="Minimum Amount Spent (GHS)" placeholder="100.00" min="0" />
                                @error('minSpent')
                                    <span class="mt-1 text-xs text-red-600">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        @if ($segmentType === 'frequent_customers')
                            <div>
                                <flux:input wire:model.live="minTickets" type="number"
                                    label="Minimum Number of Tickets" placeholder="3" min="1" max="100" />
                                @error('minTickets')
                                    <span class="mt-1 text-xs text-red-600">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        @if ($segmentType === 'contacts')
                            <div>
                                <label class="block text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-2">
                                    Select Contacts
                                </label>
                                <div
                                    class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-3 max-h-48 overflow-y-auto bg-zinc-50 dark:bg-zinc-800">
                                    @forelse($this->availableContacts as $contact)
                                        <label
                                            class="flex items-center py-2 px-3 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded cursor-pointer">
                                            <input type="checkbox" wire:model.live="selectedContactIds"
                                                value="{{ $contact['id'] }}" wire:loading.attr="disabled"
                                                wire:target="selectedContactIds"
                                                class="rounded border-zinc-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:border-zinc-600 dark:bg-zinc-700">
                                            <div class="ml-3 flex-1">
                                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ $contact['name'] }}</div>
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ $contact['phone'] }}</div>
                                            </div>
                                        </label>
                                    @empty
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400 py-4 text-center">
                                            No contacts available. <a href="#"
                                                class="text-blue-600 hover:underline">Add contacts first</a>.
                                        </div>
                                    @endforelse
                                </div>
                                @if (count($selectedContactIds) > 0)
                                    <div class="mt-2 text-xs text-zinc-600 dark:text-zinc-400">
                                        <span wire:loading.remove wire:target="selectedContactIds,calculateEstimate">
                                            {{ count($selectedContactIds) }}
                                            contact{{ count($selectedContactIds) !== 1 ? 's' : '' }} selected
                                        </span>
                                        <span wire:loading wire:target="selectedContactIds,calculateEstimate"
                                            class="text-blue-600">
                                            Updating selection...
                                        </span>
                                    </div>
                                @endif
                                @error('selectedContactIds')
                                    <span class="mt-1 text-xs text-red-600">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Scheduling --}}
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                    <h3 class="mb-4 text-lg font-medium text-zinc-900 dark:text-zinc-100">Scheduling</h3>

                    <div class="space-y-4">
                        <div>
                            <flux:checkbox wire:model.live="scheduleForLater" label="Schedule for later" />
                        </div>

                        @if ($scheduleForLater)
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <flux:input wire:model="scheduledDate" type="date" label="Date"
                                        min="{{ date('Y-m-d') }}" required />
                                    @error('scheduledDate')
                                        <span class="mt-1 text-xs text-red-600">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <flux:input wire:model="scheduledTime" type="time" label="Time" required />
                                    @error('scheduledTime')
                                        <span class="mt-1 text-xs text-red-600">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar - Estimate & Actions --}}
            <div class="space-y-6">
                {{-- Estimate Card --}}
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                    <h3 class="mb-4 text-lg font-medium text-zinc-900 dark:text-zinc-100">Campaign Estimate</h3>

                    <div class="space-y-4">
                        <div>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400">Recipients</div>
                            <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                                <span wire:loading.remove
                                    wire:target="calculateEstimate,selectedContactIds,segmentType,recentDays">
                                    {{ $estimatedRecipients !== null ? number_format($estimatedRecipients) : '-' }}
                                </span>
                                <span wire:loading
                                    wire:target="calculateEstimate,selectedContactIds,segmentType,recentDays"
                                    class="text-zinc-400">
                                    Calculating...
                                </span>
                            </div>
                            <div class="text-xs text-zinc-500">
                                @if ($segmentType === 'contacts')
                                    contacts selected
                                @elseif($segmentType === 'high_value')
                                    high-value customers
                                @elseif($segmentType === 'frequent_customers')
                                    frequent customers
                                @elseif($segmentType === 'recent')
                                    recent customers
                                @elseif($segmentType === 'active')
                                    active customers
                                @else
                                    customers with SMS enabled
                                @endif
                            </div>
                        </div>

                        <div class="border-t border-zinc-200 pt-4 dark:border-zinc-800">
                            <div class="text-sm text-zinc-600 dark:text-zinc-400">Estimated Cost</div>
                            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                                <span wire:loading.remove
                                    wire:target="calculateEstimate,selectedContactIds,segmentType,recentDays,message">
                                    @if ($estimatedCost !== null)
                                        {{ format_currency($estimatedCost) }}
                                    @else
                                        -
                                    @endif
                                </span>
                                <span wire:loading
                                    wire:target="calculateEstimate,selectedContactIds,segmentType,recentDays,message"
                                    class="text-emerald-400">
                                    Calculating...
                                </span>
                            </div>
                            @if ($message && $estimatedRecipients && $estimatedRecipients > 0 && $estimatedCost)
                                <div class="text-xs text-zinc-500">
                                    <span wire:loading.remove
                                        wire:target="calculateEstimate,selectedContactIds,segmentType,recentDays,message">
                                        ${{ number_format($estimatedCost / $estimatedRecipients, 4) }} per SMS
                                    </span>
                                </div>
                            @endif
                        </div>

                        @if ($estimatedRecipients === 0)
                            <div
                                class="rounded-lg bg-yellow-50 p-3 text-xs text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200">
                                <strong>Warning:</strong> No recipients found matching your criteria.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="space-y-3">
                    <flux:button type="submit" variant="primary" class="w-full"
                        x-bind:disabled="{{ $estimatedRecipients === 0 ? 'true' : 'false' }}"
                        wire:loading.attr="disabled" wire:target="create">
                        <span wire:loading.remove wire:target="create">
                            @if ($scheduleForLater)
                                Schedule Campaign
                            @else
                                Send Campaign Now
                            @endif
                        </span>
                        <span wire:loading wire:target="create">
                            @if ($scheduleForLater)
                                Scheduling...
                            @else
                                Starting Campaign...
                            @endif
                        </span>
                    </flux:button>

                    <a href="{{ route('admin.sms-campaigns') }}" class="block">
                        <flux:button variant="ghost" class="w-full">
                            Cancel
                        </flux:button>
                    </a>
                </div>

                {{-- Help Text --}}
                <div class="rounded-lg bg-blue-50 p-4 text-xs text-blue-800 dark:bg-blue-900/20 dark:text-blue-200">
                    <strong class="block mb-1">Tips:</strong>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Keep messages under 160 characters for 1 SMS segment</li>
                        <li>Avoid special characters to reduce segments</li>
                        <li>Preview will show estimated segments</li>
                        <li>Use templates for common message types</li>
                        <li>Template variables like {customer_name} will be replaced with actual values</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>

    {{-- Preview Modal --}}
    @if ($showPreview)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-zinc-500 bg-opacity-75 transition-opacity" wire:click="closePreview">
                </div>

                <div
                    class="inline-block align-bottom bg-white dark:bg-zinc-900 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100" id="modal-title">
                                Message Preview
                            </h3>
                            <flux:button variant="ghost" size="sm" wire:click="closePreview" icon="x-mark">
                            </flux:button>
                        </div>

                        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4 mb-4">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400 mb-2">SMS Preview:</div>
                            <div class="text-sm text-zinc-900 dark:text-zinc-100 whitespace-pre-wrap">
                                {{ $previewMessage }}</div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-xs text-zinc-600 dark:text-zinc-400">
                            <div>
                                <div class="font-medium">Characters:</div>
                                <div>{{ strlen($previewMessage) }}</div>
                            </div>
                            <div>
                                <div class="font-medium">Segments:</div>
                                <div>{{ $this->getSegmentCount() }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6">
                        <flux:button variant="primary" class="w-full" wire:click="closePreview">
                            Close Preview
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Test Send Modal --}}
    @if ($showTestSend)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-zinc-500 bg-opacity-75 transition-opacity" wire:click="closeTestSend">
                </div>

                <div
                    class="inline-block align-bottom bg-white dark:bg-zinc-900 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100" id="modal-title">
                                Send Test Message
                            </h3>
                            <flux:button variant="ghost" size="sm" wire:click="closeTestSend" icon="x-mark">
                            </flux:button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <flux:input wire:model="testPhoneNumber" label="Test Phone Number"
                                    placeholder="+1234567890" type="tel" />
                                @error('testPhoneNumber')
                                    <span class="mt-1 text-xs text-red-600">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4">
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 mb-2">Message to send:</div>
                                <div class="text-sm text-zinc-900 dark:text-zinc-100 whitespace-pre-wrap">
                                    {{ $previewMessage }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 flex gap-3">
                        <flux:button variant="ghost" class="flex-1" wire:click="closeTestSend">
                            Cancel
                        </flux:button>
                        <flux:button variant="primary" class="flex-1" wire:click="sendTest"
                            wire:loading.attr="disabled" wire:target="sendTest">
                            <span wire:loading.remove wire:target="sendTest">Send Test</span>
                            <span wire:loading wire:target="sendTest">Sending...</span>
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
