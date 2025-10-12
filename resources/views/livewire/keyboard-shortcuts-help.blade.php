<div x-data="{ isOpen: @entangle('isOpen') }" x-show="isOpen" x-on:keydown.escape.window="$wire.close()"
    x-on:toggle-shortcuts-help.window="$wire.toggle()" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 px-4 backdrop-blur-sm"
    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    <div x-on:click.outside="$wire.close()"
        class="w-full max-w-3xl rounded-lg border border-zinc-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-800"
        x-transition:enter="transition ease-out duration-200 delay-100" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

        <!-- Header -->
        <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
            <div>
                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">Keyboard Shortcuts</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Quick reference for power users</p>
            </div>
            <button wire:click="close" type="button"
                class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Content -->
        <div class="max-h-[calc(100vh-16rem)] overflow-y-auto p-6">
            <div class="grid gap-8 md:grid-cols-2">
                @foreach ($shortcuts as $category => $items)
                    <div>
                        <h3
                            class="mb-3 text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ $category }}
                        </h3>
                        <div class="space-y-2">
                            @foreach ($items as $shortcut)
                                <div
                                    class="flex items-center justify-between rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">
                                        {{ $shortcut['description'] }}
                                    </span>
                                    <div class="flex items-center gap-1">
                                        @foreach ($shortcut['keys'] as $key)
                                            <kbd
                                                class="rounded bg-white px-2.5 py-1.5 text-xs font-semibold text-zinc-700 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700">
                                                {{ $key }}
                                            </kbd>
                                            @if (!$loop->last)
                                                <span class="text-xs text-zinc-400">then</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pro Tips -->
            <div class="mt-8 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-900/20">
                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-blue-600 dark:text-blue-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100">Pro Tips</h4>
                        <ul class="mt-2 space-y-1 text-sm text-blue-700 dark:text-blue-300">
                            <li>• Press <kbd
                                    class="rounded bg-white px-1.5 py-0.5 text-xs font-semibold shadow-sm dark:bg-blue-950">Ctrl+K</kbd>
                                to quickly navigate anywhere</li>
                            <li>• Press <kbd
                                    class="rounded bg-white px-1.5 py-0.5 text-xs font-semibold shadow-sm dark:bg-blue-950">G</kbd>
                                then another key for quick navigation (e.g., G then D for Dashboard)</li>
                            <li>• Shortcuts work globally except when typing in form fields</li>
                            <li>• On Mac, use <kbd
                                    class="rounded bg-white px-1.5 py-0.5 text-xs font-semibold shadow-sm dark:bg-blue-950">Cmd</kbd>
                                instead of <kbd
                                    class="rounded bg-white px-1.5 py-0.5 text-xs font-semibold shadow-sm dark:bg-blue-950">Ctrl</kbd>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-zinc-200 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Press <kbd
                        class="rounded bg-white px-2 py-1 text-xs font-semibold shadow-sm dark:bg-zinc-800">?</kbd> to
                    show this help again
                </p>
                <button wire:click="close" type="button"
                    class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
