<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="mb-2">SMS Campaigns</flux:heading>
            <flux:subheading>Send bulk SMS messages to customer segments</flux:subheading>
        </div>
        <div>
            <a href="{{ route('admin.sms-campaigns.create') }}">
                <flux:button variant="primary" icon="plus">
                    New Campaign
                </flux:button>
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div
            class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search campaigns..."
                    icon="magnifying-glass" />
            </div>
            <div>
                <flux:select wire:model.live="statusFilter">
                    <option value="all">All Statuses</option>
                    <option value="draft">Draft</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="sending">Sending</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </flux:select>
            </div>
        </div>
    </div>

    {{-- Campaigns Table --}}
    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        @if ($campaigns->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                Campaign
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                Progress
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                Cost
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                Created
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                        @foreach ($campaigns as $campaign)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $campaign->name }}
                                    </div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400 truncate max-w-md">
                                        {{ Str::limit($campaign->message, 80) }}
                                    </div>
                                    <div class="mt-1 text-xs text-zinc-400">
                                        By {{ $campaign->creator->name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        {{ match ($campaign->status) {
                                            'draft' => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300',
                                            'scheduled' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300',
                                            'sending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300',
                                            'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300',
                                            'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300',
                                            default => 'bg-zinc-100 text-zinc-800',
                                        } }}">
                                        {{ ucfirst($campaign->status) }}
                                    </span>
                                    @if ($campaign->scheduled_at)
                                        <div class="mt-1 text-xs text-zinc-500">
                                            {{ $campaign->scheduled_at->format('M d, Y H:i') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $campaign->sent_count + $campaign->failed_count }} /
                                        {{ $campaign->total_recipients }}
                                    </div>
                                    @if ($campaign->total_recipients > 0)
                                        <div
                                            class="mt-1 h-2 w-32 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                            <div class="h-full bg-emerald-500 transition-all duration-300"
                                                style="width: {{ $campaign->progress_percentage }}%">
                                            </div>
                                        </div>
                                        <div class="mt-1 text-xs text-zinc-500">
                                            {{ $campaign->success_rate }}% success
                                        </div>
                                    @else
                                        <div class="text-xs text-zinc-500">Not started</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($campaign->actual_cost)
                                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ format_currency($campaign->actual_cost) }}
                                        </div>
                                        <div class="text-xs text-zinc-500">Actual</div>
                                    @elseif ($campaign->estimated_cost)
                                        <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                            ~{{ format_currency($campaign->estimated_cost) }}
                                        </div>
                                        <div class="text-xs text-zinc-500">Estimated</div>
                                    @else
                                        <span class="text-sm text-zinc-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $campaign->created_at->format('M d, Y') }}
                                    </div>
                                    <div class="text-xs text-zinc-500">
                                        {{ $campaign->created_at->format('H:i') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        @if (in_array($campaign->status, ['draft', 'scheduled']))
                                            <flux:button variant="ghost" size="sm"
                                                wire:click="cancelCampaign('{{ $campaign->id }}')"
                                                wire:confirm="Are you sure you want to cancel this campaign?">
                                                Cancel
                                            </flux:button>
                                        @endif

                                        @if (in_array($campaign->status, ['draft', 'completed', 'cancelled']))
                                            <flux:button variant="ghost" size="sm"
                                                wire:click="confirmDelete('{{ $campaign->id }}')"
                                                class="text-red-600 hover:text-red-700 dark:text-red-400">
                                                Delete
                                            </flux:button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="border-t border-zinc-200 px-6 py-4 dark:border-zinc-800">
                {{ $campaigns->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">No campaigns</h3>
                <p class="mt-1 text-sm text-zinc-500">Get started by creating a new SMS campaign.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.sms-campaigns.create') }}">
                        <flux:button variant="primary" icon="plus">
                            New Campaign
                        </flux:button>
                    </a>
                </div>
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            wire:click="$set('showDeleteConfirm', false)">
            <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-zinc-900" wire:click.stop>
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Delete Campaign</h3>
                <p class="mt-2 text-sm text-zinc-500">
                    Are you sure you want to delete this campaign? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <flux:button variant="ghost" wire:click="$set('showDeleteConfirm', false)">
                        Cancel
                    </flux:button>
                    <flux:button variant="primary" wire:click="deleteCampaign" class="bg-red-600 hover:bg-red-700">
                        Delete
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
