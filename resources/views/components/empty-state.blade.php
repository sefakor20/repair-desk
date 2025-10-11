@props([
    'icon' => 'inbox',
    'title' => 'No items found',
    'description' => 'Get started by creating your first item',
    'action' => null,
    'actionLabel' => null,
    'actionRoute' => null,
])

<div class="flex flex-col items-center justify-center py-12 px-6">
    {{-- Animated Icon --}}
    <div class="relative">
        <div class="absolute inset-0 animate-ping rounded-full bg-zinc-200 opacity-20 dark:bg-zinc-700"></div>
        <div class="relative flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
            @if ($icon === 'inbox')
                <svg class="h-8 w-8 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
            @elseif($icon === 'users')
                <svg class="h-8 w-8 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            @elseif($icon === 'document')
                <svg class="h-8 w-8 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            @elseif($icon === 'device')
                <svg class="h-8 w-8 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            @elseif($icon === 'search')
                <svg class="h-8 w-8 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            @endif
        </div>
    </div>

    {{-- Content --}}
    <h3 class="mt-4 text-base font-semibold text-zinc-900 dark:text-white">{{ $title }}</h3>
    <p class="mt-2 text-center text-sm text-zinc-600 dark:text-zinc-400">{{ $description }}</p>

    {{-- Action Button --}}
    @if ($action || $actionRoute)
        <div class="mt-6">
            @if ($actionRoute)
                <a href="{{ $actionRoute }}" wire:navigate
                    class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-zinc-800 hover:shadow-lg dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ $actionLabel ?? 'Create New' }}
                </a>
            @else
                {{ $action }}
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
