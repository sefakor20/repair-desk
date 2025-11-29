<div>
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <flux:button :href="route('admin.contacts.index')" variant="ghost" icon="arrow-left" size="sm">
                Back to Contacts
            </flux:button>
        </div>
        <div class="mt-4">
            <flux:heading size="lg">Edit Contact</flux:heading>
            <flux:text variant="muted">Update contact information</flux:text>
        </div>
    </div>

    <div class="max-w-2xl">
        <form wire:submit="save" class="space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Contact Information</h3>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <flux:field>
                            <flux:label>First Name *</flux:label>
                            <flux:input wire:model="first_name" placeholder="First name" />
                            <flux:error name="first_name" />
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>Last Name</flux:label>
                            <flux:input wire:model="last_name" placeholder="Last name" />
                            <flux:error name="last_name" />
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>Email</flux:label>
                            <flux:input wire:model="email" type="email" placeholder="email@example.com" />
                            <flux:error name="email" />
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>Phone</flux:label>
                            <flux:input wire:model="phone" placeholder="+233 XX XXX XXXX" />
                            <flux:error name="phone" />
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>Company</flux:label>
                            <flux:input wire:model="company" placeholder="Company name" />
                            <flux:error name="company" />
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>Position</flux:label>
                            <flux:input wire:model="position" placeholder="Job title" />
                            <flux:error name="position" />
                        </flux:field>
                    </div>

                    <div class="sm:col-span-2">
                        <flux:field>
                            <flux:checkbox wire:model="is_active">
                                Active contact
                            </flux:checkbox>
                            <flux:text variant="muted" class="mt-1">
                                Only active contacts can receive SMS messages
                            </flux:text>
                        </flux:field>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <flux:button :href="route('admin.contacts.index')" variant="ghost">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Update Contact
                </flux:button>
            </div>
        </form>
    </div>
</div>
