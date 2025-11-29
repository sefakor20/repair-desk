<div>
    <flux:header container class="space-y-2">
        <div>
            <flux:heading size="xl" level="1">{{ __('SMS Templates') }}</flux:heading>
            <flux:subheading>{{ __('Manage reusable SMS message templates') }}</flux:subheading>
        </div>

        <flux:spacer />

        <flux:button :href="route('admin.sms-templates.create')" icon="plus" wire:navigate>
            {{ __('New Template') }}
        </flux:button>
    </flux:header>

    <flux:main container>
        {{-- Search Bar --}}
        <div class="mb-6">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search templates...') }}"
                icon="magnifying-glass" />
        </div>

        {{-- Templates Table --}}
        <div class="space-y-4">
            @forelse ($templates as $template)
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $template->name }}
                                </h3>
                                <flux:badge :color="$template->is_active ? 'lime' : 'zinc'" size="sm">
                                    {{ $template->is_active ? __('Active') : __('Inactive') }}
                                </flux:badge>
                            </div>

                            <div class="mb-2">
                                <flux:badge color="zinc" size="sm">
                                    <code>{{ $template->key }}</code>
                                </flux:badge>
                            </div>

                            @if ($template->description)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-3">{{ $template->description }}
                                </p>
                            @endif

                            <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-3 mb-3">
                                <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $template->message }}</p>
                            </div>

                            @if ($template->extractVariables())
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs text-zinc-500">{{ __('Variables:') }}</span>
                                    @foreach ($template->extractVariables() as $variable)
                                        <flux:badge color="violet" size="sm">
                                            &#123;&#123;{{ $variable }}&#125;&#125;
                                        </flux:badge>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            <flux:button variant="ghost" size="sm" wire:click="toggleStatus({{ $template->id }})"
                                icon="{{ $template->is_active ? 'eye-slash' : 'eye' }}">
                                {{ $template->is_active ? __('Deactivate') : __('Activate') }}
                            </flux:button>

                            <flux:button variant="ghost" size="sm"
                                :href="route('admin.sms-templates.edit', $template)" icon="pencil" wire:navigate>
                                {{ __('Edit') }}
                            </flux:button>

                            <flux:button variant="ghost" size="sm" wire:click="delete({{ $template->id }})"
                                wire:confirm="{{ __('Are you sure you want to delete this template?') }}"
                                icon="trash" color="red">
                                {{ __('Delete') }}
                            </flux:button>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex flex-col items-center gap-4">
                        <div class="rounded-full bg-zinc-100 p-4 dark:bg-zinc-800">
                            <svg class="h-8 w-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                                {{ __('No templates found') }}
                            </h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $search ? __('Try adjusting your search criteria') : __('Get started by creating your first SMS template') }}
                            </p>
                        </div>
                        @if (!$search)
                            <flux:button :href="route('admin.sms-templates.create')" icon="plus" wire:navigate>
                                {{ __('Create Template') }}
                            </flux:button>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($templates->hasPages())
            <div class="mt-6">
                {{ $templates->links() }}
            </div>
        @endif
    </flux:main>
</div>
