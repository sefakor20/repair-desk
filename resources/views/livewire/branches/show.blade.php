<div class="max-w-2xl space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $branch->name }}</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Branch details and location information.</p>
    </div>
    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6 shadow space-y-4">
        <dl class="divide-y divide-zinc-100 dark:divide-zinc-700">
            <div class="py-2 flex items-center justify-between">
                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Code</dt>
                <dd class="text-zinc-900 dark:text-white">{{ $branch->code }}</dd>
            </div>
            <div class="py-2 flex items-center justify-between">
                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Address</dt>
                <dd class="text-zinc-900 dark:text-white">{{ $branch->full_address }}</dd>
            </div>
            <div class="py-2 flex items-center justify-between">
                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Phone</dt>
                <dd class="text-zinc-900 dark:text-white">{{ $branch->phone }}</dd>
            </div>
            <div class="py-2 flex items-center justify-between">
                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Email</dt>
                <dd class="text-zinc-900 dark:text-white">{{ $branch->email }}</dd>
            </div>
            <div class="py-2 flex items-center justify-between">
                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Status</dt>
                <dd>
                    <span
                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $branch->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                        {{ $branch->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </dd>
            </div>
            <div class="py-2 flex items-center justify-between">
                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Main Branch</dt>
                <dd>
                    <span
                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $branch->is_main ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300' }}">
                        {{ $branch->is_main ? 'Yes' : 'No' }}
                    </span>
                </dd>
            </div>
            <div class="py-2 flex items-center justify-between">
                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Users</dt>
                <dd class="text-zinc-900 dark:text-white">{{ $branch->users_count }}</dd>
            </div>
            <div class="py-2 flex items-center justify-between">
                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Tickets</dt>
                <dd class="text-zinc-900 dark:text-white">{{ $branch->tickets_count }}</dd>
            </div>
            <div class="py-2 flex items-center justify-between">
                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Inventory</dt>
                <dd class="text-zinc-900 dark:text-white">{{ $branch->inventory_items_count }}</dd>
            </div>
            <div class="py-2 flex items-center justify-between">
                <dt class="font-medium text-zinc-500 dark:text-zinc-400">POS Sales</dt>
                <dd class="text-zinc-900 dark:text-white">{{ $branch->pos_sales_count }}</dd>
            </div>
        </dl>
        @if ($branch->notes)
            <div class="mt-4">
                <div class="rounded-lg bg-zinc-50 p-4 text-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                    {{ $branch->notes }}
                </div>
            </div>
        @endif
        <div class="flex justify-end gap-2 mt-6">
            <button type="button" onclick="window.history.back()"
                class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                Close
            </button>
            <a href="{{ route('branches.edit', $branch) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-zinc-800 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 dark:focus:ring-white">
                Edit Branch
            </a>
        </div>
    </div>
</div>
