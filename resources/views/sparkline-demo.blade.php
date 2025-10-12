<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sparkline Demo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-zinc-50 p-8 dark:bg-zinc-900">
    <div class="mx-auto max-w-4xl">
        <h1 class="mb-8 text-3xl font-bold text-zinc-900 dark:text-white">Sparkline Component Demo</h1>

        <div class="grid gap-6 sm:grid-cols-2">
            {{-- Green Upward Trend --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Upward Revenue Trend</h3>
                <div class="mb-2 text-3xl font-bold text-zinc-900 dark:text-white">$12,450</div>
                <x-trend-indicator :trend="['percentage' => 28.2, 'direction' => 'up', 'isPositive' => true]" :sparkline-data="[300, 450, 520, 610, 580, 720, 850]" />
            </div>

            {{-- Red Downward Trend --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Declining Tickets</h3>
                <div class="mb-2 text-3xl font-bold text-zinc-900 dark:text-white">12</div>
                <x-trend-indicator :trend="['percentage' => 15.5, 'direction' => 'down', 'isPositive' => false]" :sparkline-data="[45, 42, 38, 35, 32, 28, 25]" />
            </div>

            {{-- Green Downward (Good) --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Low Stock Improving</h3>
                <div class="mb-2 text-3xl font-bold text-zinc-900 dark:text-white">5</div>
                <x-trend-indicator :trend="['percentage' => 50, 'direction' => 'down', 'isPositive' => true]" :sparkline-data="[18, 16, 14, 12, 10, 7, 5]" label="improvement" />
            </div>

            {{-- Neutral --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Stable Metrics</h3>
                <div class="mb-2 text-3xl font-bold text-zinc-900 dark:text-white">25</div>
                <x-trend-indicator :trend="['percentage' => 0, 'direction' => 'neutral', 'isPositive' => true]" :sparkline-data="[25, 25, 26, 25, 24, 25, 25]" />
            </div>

            {{-- Volatile Pattern --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Volatile Pattern</h3>
                <div class="mb-2 text-3xl font-bold text-zinc-900 dark:text-white">$8,200</div>
                <x-trend-indicator :trend="['percentage' => 12, 'direction' => 'up', 'isPositive' => true]" :sparkline-data="[500, 800, 600, 900, 550, 850, 700]" />
            </div>

            {{-- Sharp Growth --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Sharp Growth</h3>
                <div class="mb-2 text-3xl font-bold text-zinc-900 dark:text-white">1,250</div>
                <x-trend-indicator :trend="['percentage' => 125, 'direction' => 'up', 'isPositive' => true]" :sparkline-data="[100, 150, 200, 350, 600, 900, 1250]" />
            </div>
        </div>
    </div>
</body>

</html>
