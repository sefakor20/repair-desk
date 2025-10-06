<div class="max-w-3xl space-y-6">
    <!-- Header -->
    <div>
        <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
            <a href="{{ route('customers.index') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-white">
                Customers
            </a>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span>New Customer</span>
        </div>
        <h1 class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">Create Customer</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Add a new customer to your database</p>
    </div>

    <!-- Form -->
    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <x-customer-form action="Create" />
    </div>
</div>
