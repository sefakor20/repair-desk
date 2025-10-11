<div>
    <div class="mb-6">
        <flux:heading size="xl">Create User</flux:heading>
        <flux:subheading>Add a new user to the system</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
            <div class="grid gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <flux:field>
                        <flux:label>Name *</flux:label>
                        <flux:input wire:model="name" placeholder="Enter full name" />
                        <flux:error name="name" />
                    </flux:field>
                </div>

                <div class="sm:col-span-2">
                    <flux:field>
                        <flux:label>Email *</flux:label>
                        <flux:input type="email" wire:model="email" placeholder="email@example.com" />
                        <flux:error name="email" />
                    </flux:field>
                </div>

                <div>
                    <flux:field>
                        <flux:label>Password *</flux:label>
                        <flux:input type="password" wire:model="password" placeholder="Enter password" />
                        <flux:error name="password" />
                    </flux:field>
                </div>

                <div>
                    <flux:field>
                        <flux:label>Confirm Password *</flux:label>
                        <flux:input type="password" wire:model="password_confirmation"
                            placeholder="Re-enter password" />
                        <flux:error name="password_confirmation" />
                    </flux:field>
                </div>

                <div>
                    <flux:field>
                        <flux:label>Role *</flux:label>
                        <flux:select wire:model="role">
                            @foreach ($roles as $roleOption)
                                <option value="{{ $roleOption->value }}">{{ $roleOption->label() }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="role" />
                    </flux:field>
                </div>

                <div>
                    <flux:field>
                        <flux:label>Phone</flux:label>
                        <flux:input type="tel" wire:model="phone" placeholder="Enter phone number" />
                        <flux:error name="phone" />
                    </flux:field>
                </div>

                <div class="sm:col-span-2">
                    <flux:field>
                        <flux:label>Status</flux:label>
                        <div class="flex items-center gap-2">
                            <flux:checkbox wire:model="active" />
                            <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
                        </div>
                        <flux:error name="active" />
                    </flux:field>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <flux:button href="{{ route('users.index') }}" wire:navigate variant="ghost">
                Cancel
            </flux:button>

            <flux:button type="submit" variant="primary" icon="check">
                Create User
            </flux:button>
        </div>
    </form>
</div>
