@props(['target' => null])

<div
    {{ $attributes->merge([
        'class' =>
            'absolute inset-0 z-10 flex items-center justify-center bg-white/80 backdrop-blur-sm dark:bg-zinc-900/80',
        'wire:loading' => $target ? "wire:target=$target" : true,
    ]) }}>
    <div class="flex flex-col items-center gap-3">
        <div class="relative h-10 w-10">
            <div
                class="absolute inset-0 animate-spin rounded-full border-4 border-zinc-200 border-t-zinc-900 dark:border-zinc-700 dark:border-t-white">
            </div>
        </div>
        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Loading...</p>
    </div>
</div>
