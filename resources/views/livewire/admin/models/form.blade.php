<div>
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-4">
            <flux:button :href="route('admin.models.index')" variant="ghost" icon="arrow-left" size="sm">
                Back to Models
            </flux:button>
        </div>

        <div>
            <flux:heading size="lg">{{ $isEditing ? 'Edit Model' : 'Create Model' }}</flux:heading>
            <flux:text variant="muted">
                {{ $isEditing ? 'Update model information' : 'Add a new device model to the system' }}
            </flux:text>
        </div>
    </div>

    <div class="max-w-2xl">
        <form wire:submit="save" class="space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 space-y-6">
                <!-- Category -->
                <div>
                    <flux:select wire:model.live="category" label="Device Category" required>
                        @foreach ($categories as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    @error('category')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                    <flux:description>Select the device category first to filter brands</flux:description>
                </div>

                <!-- Brand -->
                <div>
                    <flux:select wire:model="brand_id" label="Brand" required>
                        <option value="">Select a brand</option>
                        @foreach ($this->brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </flux:select>
                    @error('brand_id')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                    @if ($this->brands->isEmpty())
                        <flux:description class="text-orange-600 dark:text-orange-400">
                            No active brands found for this category. Please create a brand first.
                        </flux:description>
                    @else
                        <flux:description>Select the brand that manufactures this model</flux:description>
                    @endif
                </div>

                <!-- Model Name -->
                <div>
                    <flux:input wire:model="name" label="Model Name"
                        placeholder="e.g., iPhone 15 Pro, MacBook Pro 14-inch, ThinkPad X1 Carbon" required />
                    @error('name')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </div>

                <!-- Specifications -->
                <div class="space-y-4">
                    <flux:subheading>Specifications (Optional)</flux:subheading>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:input wire:model="specifications.storage" label="Storage"
                                placeholder="e.g., 256GB, 512GB, 1TB" />
                        </div>

                        <div>
                            <flux:input wire:model="specifications.ram" label="RAM"
                                placeholder="e.g., 8GB, 16GB, 32GB" />
                        </div>

                        <div>
                            <flux:input wire:model="specifications.screen_size" label="Screen Size"
                                placeholder="e.g., 6.1 inches, 14 inches" />
                        </div>

                        <div>
                            <flux:input wire:model="specifications.processor" label="Processor"
                                placeholder="e.g., M3 Pro, Intel i7" />
                        </div>
                    </div>

                    <flux:description>Add common specifications to help identify this model
                    </flux:description>
                </div>

                <!-- Active Status -->
                <div>
                    <flux:checkbox wire:model="is_active" label="Active" />
                    <flux:description>Inactive models won't appear in device selection dropdowns
                    </flux:description>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-3">
                <flux:button :href="route('admin.models.index')" variant="ghost">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $isEditing ? 'Update Model' : 'Create Model' }}
                </flux:button>
            </div>
        </form>
    </div>
</div>
