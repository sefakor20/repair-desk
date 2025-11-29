<div>
    <flux:header container class="space-y-2">
        <div>
            <flux:heading size="xl" level="1">
                {{ $isEditing ? __('Edit SMS Template') : __('New SMS Template') }}
            </flux:heading>
            <flux:subheading>
                {{ __('Create reusable message templates with dynamic variables') }}
            </flux:subheading>
        </div>
    </flux:header>

    <flux:main container>
        <form wire:submit="save" class="space-y-6">
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="space-y-6">
                    {{-- Name --}}
                    <flux:field>
                        <flux:label>{{ __('Template Name') }}</flux:label>
                        <flux:input wire:model="name" placeholder="{{ __('e.g., Repair Complete Notification') }}" />
                        <flux:error name="name" />
                    </flux:field>

                    {{-- Key --}}
                    <flux:field>
                        <flux:label>{{ __('Template Key') }}</flux:label>
                        <flux:input wire:model="key" placeholder="{{ __('e.g., repair_complete') }}" />
                        <flux:description>
                            {{ __('Unique identifier used in code. Use lowercase with underscores.') }}
                        </flux:description>
                        <flux:error name="key" />
                    </flux:field>

                    {{-- Description --}}
                    <flux:field>
                        <flux:label>{{ __('Description') }}</flux:label>
                        <flux:textarea wire:model="description" rows="2"
                            placeholder="{{ __('Brief description of when this template is used...') }}" />
                        <flux:error name="description" />
                    </flux:field>

                    {{-- Message --}}
                    <flux:field>
                        <flux:label>{{ __('Message Template') }}</flux:label>
                        <flux:textarea wire:model.live="message" rows="4"
                            placeholder="Your message with &#123;&#123;variables&#125;&#125; here..." />
                        <flux:description>
                            Use &#123;&#123;variable_name&#125;&#125; for dynamic content. Example: Hello
                            &#123;&#123;customer_name&#125;&#125;, your &#123;&#123;ticket_number&#125;&#125; is ready!
                        </flux:description>
                        <flux:error name="message" />
                    </flux:field>

                    {{-- Detected Variables --}}
                    @if (count($detectedVariables) > 0)
                        <div
                            class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="mb-2 flex items-center gap-2">
                                <flux:icon.information-circle class="size-5 text-blue-500" />
                                <span class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ __('Detected Variables') }}
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($detectedVariables as $variable)
                                    <flux:badge color="violet">
                                        &#123;&#123;{{ $variable }}&#125;&#125;
                                    </flux:badge>
                                @endforeach
                            </div>
                            <p class="mt-2 text-xs text-zinc-600 dark:text-zinc-400">
                                {{ __('These variables will be replaced with actual values when the template is used.') }}
                            </p>
                        </div>
                    @endif

                    {{-- Character Count --}}
                    <div class="text-sm text-zinc-600 dark:text-zinc-400">
                        {{ __('Message length:') }} <span class="font-semibold">{{ strlen($message) }}</span>
                        {{ __('characters') }}
                        @if (strlen($message) > 160)
                            <span class="text-amber-600 dark:text-amber-400">
                                ({{ __('Multiple SMS will be sent') }})
                            </span>
                        @endif
                    </div>

                    {{-- Active Status --}}
                    <flux:field>
                        <div class="flex items-center gap-3">
                            <flux:switch wire:model="is_active" />
                            <flux:label>{{ __('Active') }}</flux:label>
                        </div>
                        <flux:description>
                            {{ __('Only active templates can be used for sending messages.') }}
                        </flux:description>
                    </flux:field>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between">
                <flux:button variant="ghost" :href="route('admin.sms-templates.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button type="submit" variant="primary">
                    {{ $isEditing ? __('Update Template') : __('Create Template') }}
                </flux:button>
            </div>
        </form>
    </flux:main>
</div>
