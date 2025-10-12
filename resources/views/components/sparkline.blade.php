@props(['data' => [], 'color' => 'green', 'height' => 32, 'width' => 100])

@php
    // Ensure we have data
    if (empty($data)) {
        $data = [0];
    }

    // Determine color classes
    $strokeColor = match ($color) {
        'red' => '#ef4444',
        'green' => '#10b981',
        'amber' => '#f59e0b',
        'orange' => '#f97316',
        'blue' => '#3b82f6',
        default => '#10b981',
    };

    // Calculate dimensions
    $padding = 2;
    $max = max($data);
    $min = min($data);
    $range = $max - $min;

    // Prevent division by zero
    if ($range == 0) {
        $range = 1;
    }

    // Calculate points for polyline
    $points = [];
    $count = count($data);
    $xStep = ($width - $padding * 2) / max($count - 1, 1);

    foreach ($data as $index => $value) {
        $x = $padding + $index * $xStep;
        $y = $height - $padding - (($value - $min) / $range) * ($height - $padding * 2);
        $points[] = "$x,$y";
    }

    $pointsStr = implode(' ', $points);
@endphp

<svg width="{{ $width }}" height="{{ $height }}" class="block"
    viewBox="0 0 {{ $width }} {{ $height }}">
    <polyline fill="none" stroke="{{ $strokeColor }}" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
        points="{{ $pointsStr }}" class="transition-all duration-300" />
</svg>
