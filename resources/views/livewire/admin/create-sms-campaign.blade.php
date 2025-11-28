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
                        </div>
                    </div>
                </div>

                {{-- Audience Targeting --}}
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                    <h3 class="mb-4 text-lg font-medium text-zinc-900 dark:text-zinc-100">Target Audience</h3>

                    <div class="space-y-4">
                        <div>
                            <flux:select wire:model.live="segmentType" label="Customer Segment">
                                <option value="all">All Customers</option>
                                <option value="recent">Recent Customers</option>
                                <option value="active">Active Customers (with recent tickets)</option>
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
                                <span wire:loading.remove wire:target="calculateEstimate">
                                    {{ $estimatedRecipients !== null ? number_format($estimatedRecipients) : '-' }}
                                </span>
                                <span wire:loading wire:target="calculateEstimate" class="text-zinc-400">
                                    Calculating...
                                </span>
                            </div>
                            <div class="text-xs text-zinc-500">customers with SMS enabled</div>
                        </div>

                        <div class="border-t border-zinc-200 pt-4 dark:border-zinc-800">
                            <div class="text-sm text-zinc-600 dark:text-zinc-400">Estimated Cost</div>
                            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                                <span wire:loading.remove wire:target="calculateEstimate">
                                    @if ($estimatedCost !== null)
                                        ${{ number_format($estimatedCost, 2) }}
                                    @else
                                        -
                                    @endif
                                </span>
                                <span wire:loading wire:target="calculateEstimate" class="text-emerald-400">
                                    Calculating...
                                </span>
                            </div>
                            @if ($message && $estimatedRecipients && $estimatedRecipients > 0 && $estimatedCost)
                                <div class="text-xs text-zinc-500">
                                    ${{ number_format($estimatedCost / $estimatedRecipients, 4) }} per SMS
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
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>
