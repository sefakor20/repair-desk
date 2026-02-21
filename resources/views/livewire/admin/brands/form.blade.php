<div>
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-4">
            <flux:button :href="route('admin.brands.index')" variant="ghost" icon="arrow-left" size="sm">
                Back to Brands
            </flux:button>
        </div>

        <div>
            <flux:heading size="lg">{{ $isEditing ? 'Edit Brand' : 'Create Brand' }}</flux:heading>
            <flux:text variant="muted">
                {{ $isEditing ? 'Update brand information' : 'Add a new device brand to the system' }}
            </flux:text>
        </div>
    </div>

    <div class="max-w-2xl">
        <form wire:submit="save" class="space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 space-y-6">
                <!-- Brand Name -->
                <div>
                    <flux:input wire:model="name" label="Brand Name" placeholder="e.g., Apple, Samsung, Dell"
                        required />
                    @error('name')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </div>

                <!-- Category -->
                <div>
                    <flux:select wire:model="category" label="Device Category" required>
                        @foreach ($categories as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    @error('category')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                    <flux:description>Select the type of devices this brand manufactures</flux:description>
                </div>

                <!-- Logo Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Brand Logo (Optional)
                    </label>
                    <input type="file" wire:model="logo" accept="image/*"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900 dark:file:text-blue-300" />
                    @error('logo')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror

                    @if ($logo)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Preview:</p>
                            <img src="{{ $logo->temporaryUrl() }}" class="h-16 w-auto rounded">
                        </div>
                    @elseif($isEditing && $brand->logo_path)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Current Logo:</p>
                            <img src="{{ Storage::url($brand->logo_path) }}" class="h-16 w-auto rounded">
                        </div>
                    @endif
                </div>

                <!-- Active Status -->
                <div>
                    <flux:checkbox wire:model="is_active" label="Active" />
                    <flux:description>Inactive brands won't appear in device selection dropdowns
                    </flux:description>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-3">
                <flux:button :href="route('admin.brands.index')" variant="ghost">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $isEditing ? 'Update Brand' : 'Create Brand' }}
                </flux:button>
            </div>
        </form>
    </div>
</div>
