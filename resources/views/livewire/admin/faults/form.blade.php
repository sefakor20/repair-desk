<div>
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-4">
            <flux:button :href="route('admin.faults.index')" variant="ghost" icon="arrow-left" size="sm">
                Back to Faults
            </flux:button>
        </div>

        <div>
            <flux:heading size="lg">{{ $isEditing ? 'Edit Fault' : 'Create Fault' }}</flux:heading>
            <flux:text variant="muted">
                {{ $isEditing ? 'Update fault information' : 'Add a new common fault type to the system' }}
            </flux:text>
        </div>
    </div>

    <div class="max-w-2xl">
        <form wire:submit="save" class="space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 space-y-6">
                <!-- Fault Name -->
                <div>
                    <flux:input wire:model="name" label="Fault Name"
                        placeholder="e.g., Cracked Screen, Battery Drain, Won't Turn On" required />
                    @error('name')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <flux:textarea wire:model="description" label="Description (Optional)"
                        placeholder="Provide additional details about this fault type..." rows="3" />
                    @error('description')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                    <flux:description>Help technicians understand what this fault typically involves
                    </flux:description>
                </div>

                <!-- Device Category -->
                <div>
                    <flux:select wire:model="device_category" label="Device Category" required>
                        @foreach ($categories as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    @error('device_category')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                    <flux:description>Select "Universal" if this fault applies to all device types
                    </flux:description>
                </div>

                <!-- Sort Order -->
                <div>
                    <flux:input wire:model="sort_order" type="number" label="Sort Order" min="0"
                        required />
                    @error('sort_order')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                    <flux:description>Lower numbers appear first. Use increments of 10 for easy reordering
                    </flux:description>
                </div>

                <!-- Active Status -->
                <div>
                    <flux:checkbox wire:model="is_active" label="Active" />
                    <flux:description>Inactive faults won't appear in fault selection dropdowns
                    </flux:description>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-3">
                <flux:button :href="route('admin.faults.index')" variant="ghost">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $isEditing ? 'Update Fault' : 'Create Fault' }}
                </flux:button>
            </div>
        </form>
    </div>
</div>
