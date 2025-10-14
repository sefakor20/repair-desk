<div class="max-w-4xl mx-auto">
    <div
        class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-zinc-200 dark:border-zinc-800">
            <flux:heading size="lg" class="text-zinc-900 dark:text-white">Edit Profile</flux:heading>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Update your personal information and contact details
            </p>
        </div>

        <!-- Form -->
        <form wire:submit="save" class="px-6 py-6 space-y-6">
            <!-- Name Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>First Name</flux:label>
                    <flux:input wire:model="first_name" placeholder="Enter your first name" required />
                    <flux:error name="first_name" />
                </flux:field>

                <flux:field>
                    <flux:label>Last Name</flux:label>
                    <flux:input wire:model="last_name" placeholder="Enter your last name" required />
                    <flux:error name="last_name" />
                </flux:field>
            </div>

            <!-- Email & Phone -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Email Address</flux:label>
                    <flux:input type="email" wire:model="email" placeholder="your.email@example.com" required />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>Phone Number</flux:label>
                    <flux:input type="tel" wire:model="phone" placeholder="+1 (555) 123-4567" required />
                    <flux:error name="phone" />
                </flux:field>
            </div>

            <!-- Address -->
            <flux:field>
                <flux:label>Address</flux:label>
                <flux:textarea wire:model="address" placeholder="Enter your full address (optional)" rows="3" />
                <flux:error name="address" />
            </flux:field>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:button variant="ghost" href="{{ route('portal.dashboard') }}" wire:navigate>
                    Cancel
                </flux:button>

                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Save Changes</span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Saving...
                    </span>
                </flux:button>
            </div>
        </form>
    </div>
</div>
