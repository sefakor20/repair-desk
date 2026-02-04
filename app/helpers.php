<?php

declare(strict_types=1);

use Illuminate\Support\Number;

if (! function_exists('format_currency')) {
    /**
     * Format a number as currency (GHS).
     */
    function format_currency(float|int|string $amount, ?string $currency = null): string
    {
        return Number::currency((float) $amount, $currency ?? 'GHS');
    }
}

if (! function_exists('shouldExpandGroup')) {
    /**
     * Check if a sidebar group should be expanded based on current route.
     */
    function shouldExpandGroup(array $routePatterns): bool
    {
        return collect($routePatterns)->some(fn ($pattern): bool => request()->routeIs($pattern));
    }
}
