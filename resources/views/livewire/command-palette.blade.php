<div x-data="{
    isOpen: @entangle('isOpen'),
    selectedIndex: @entangle('selectedIndex'),
}" x-show="isOpen" x-on:keydown.escape.window="$wire.close()"
    x-on:keydown.down.prevent="$wire.selectNext()" x-on:keydown.up.prevent="$wire.selectPrevious()"
    x-on:keydown.enter.prevent="$wire.executeSelected()" x-on:toggle-command-palette.window="$wire.toggle()" x-cloak
    class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 px-4 pt-20 backdrop-blur-sm"
    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    <div x-on:click.outside="$wire.close()"
        class="w-full max-w-2xl rounded-lg border border-zinc-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-800"
        x-transition:enter="transition ease-out duration-200 delay-100" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

        <!-- Search Input -->
        <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-zinc-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input wire:model.live="query" type="text" placeholder="Search commands..."
                    class="w-full rounded-lg border-0 bg-transparent py-3 pl-10 pr-4 text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-0 dark:text-white dark:placeholder-zinc-500"
                    autofocus />
                <div class="absolute right-3 top-1/2 flex -translate-y-1/2 items-center gap-1">
                    <kbd
                        class="rounded bg-zinc-100 px-2 py-1 text-xs font-semibold text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">ESC</kbd>
                </div>
            </div>
        </div>

        <!-- Commands List -->
        <div class="max-h-96 overflow-y-auto p-2">
            @if (count($filteredCommands) > 0)
                @foreach ($filteredCommands as $index => $command)
                    <button wire:click="execute({{ json_encode($command) }})" type="button"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left transition-colors {{ $selectedIndex === $index ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-zinc-50 dark:hover:bg-zinc-700/50' }}">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg {{ $selectedIndex === $index ? 'bg-blue-100 dark:bg-blue-900/40' : 'bg-zinc-100 dark:bg-zinc-700' }}">
                            <flux:icon :name="$command['icon']"
                                class="h-4 w-4 {{ $selectedIndex === $index ? 'text-blue-600 dark:text-blue-400' : 'text-zinc-600 dark:text-zinc-400' }}" />
                        </div>
                        <div class="flex-1">
                            <div
                                class="text-sm font-medium {{ $selectedIndex === $index ? 'text-blue-900 dark:text-blue-100' : 'text-zinc-900 dark:text-white' }}">
                                {{ $command['title'] }}
                            </div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $command['description'] }}
                            </div>
                        </div>
                        @if ($selectedIndex === $index)
                            <div class="flex items-center gap-1">
                                <kbd
                                    class="rounded bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-600 dark:bg-blue-900/40 dark:text-blue-400">↵</kbd>
                            </div>
                        @endif
                    </button>
                @endforeach
            @else
                <div class="py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">No commands found</p>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="border-t border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1">
                        <kbd
                            class="rounded bg-white px-2 py-1 font-semibold text-zinc-600 shadow-sm dark:bg-zinc-800 dark:text-zinc-300">↑</kbd>
                        <kbd
                            class="rounded bg-white px-2 py-1 font-semibold text-zinc-600 shadow-sm dark:bg-zinc-800 dark:text-zinc-300">↓</kbd>
                        <span>Navigate</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <kbd
                            class="rounded bg-white px-2 py-1 font-semibold text-zinc-600 shadow-sm dark:bg-zinc-800 dark:text-zinc-300">↵</kbd>
                        <span>Select</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <kbd
                            class="rounded bg-white px-2 py-1 font-semibold text-zinc-600 shadow-sm dark:bg-zinc-800 dark:text-zinc-300">ESC</kbd>
                        <span>Close</span>
                    </div>
                </div>
                <button wire:click="close" type="button"
                    class="rounded px-2 py-1 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
