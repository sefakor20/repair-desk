<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8">
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Customer Portal</h2>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Enter your email to receive a secure access link</p>
    </div>

    @if ($linkSent)
        <flux:callout variant="success" class="mb-6">
            <strong>Access link sent!</strong>
            <p class="mt-1">Check your email for a secure link to access your customer portal. The link will expire in
                30 days.</p>
        </flux:callout>
    @endif

    <form wire:submit="sendAccessLink">
        <flux:field>
            <flux:label>Email Address</flux:label>
            <flux:input wire:model="email" type="email" placeholder="you@example.com" autocomplete="email" />
            <flux:error name="email" />
        </flux:field>

        <flux:button type="submit" variant="primary" class="w-full mt-6">
            Send Access Link
        </flux:button>
    </form>

    <div class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
        <p>Don't have an account? Contact us to create one.</p>
    </div>
</div>
