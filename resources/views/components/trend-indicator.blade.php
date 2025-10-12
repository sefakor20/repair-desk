@props(['trend', 'sparklineData' => [], 'sparklineColor' => 'green', 'label' => 'vs yesterday'])

@php
    $percentage = $trend['percentage'] ?? 0;
    $direction = $trend['direction'] ?? 'neutral';
    $isPositive = $trend['isPositive'] ?? true;

    // Color classes based on positive/negative
    $colorClass = match (true) {
        $direction === 'neutral' => 'text-zinc-500 dark:text-zinc-400',
        $isPositive => 'text-green-600 dark:text-green-500',
        default => 'text-red-600 dark:text-red-500',
    };

    // Icon based on direction
    $icon = match ($direction) {
        'up' => 'arrow-trending-up',
        'down' => 'arrow-trending-down',
        default => 'minus',
    };

    // Determine sparkline color
    $sparklineColor = match (true) {
        $direction === 'neutral' => 'blue',
        $isPositive => 'green',
        default => 'red',
    };
@endphp

<div class="flex items-end justify-between gap-4">
    <div class="flex-shrink-0">
        @if ($direction !== 'neutral')
            <div class="flex flex-col gap-0.5">
                <div class="flex items-center gap-1.5 text-sm {{ $colorClass }} transition-colors">
                    <flux:icon.{{ $icon }} class="size-4" />
                    <span class="font-medium">{{ $percentage }}%</span>
                </div>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $label }}</span>
            </div>
        @else
            <div class="flex items-center gap-1.5 text-sm text-zinc-500 dark:text-zinc-400">
                <flux:icon.minus class="size-4" />
                <span>No change</span>
            </div>
        @endif
    </div>

    @if (!empty($sparklineData))
        <div class="flex-shrink-0">
            <x-sparkline :data="$sparklineData" :color="$sparklineColor" :height="32" :width="100" />
        </div>
    @endif
</div>
