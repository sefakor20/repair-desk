<div>
    <div class="mb-6">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('devices.index') }}" icon="device-phone-mobile">Devices
            </flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('devices.show', $device) }}">{{ $device->device_name }}
            </flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Edit</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>

    <div class="mb-6">
        <flux:heading size="xl">Edit Device</flux:heading>
        <flux:text class="mt-1">Update device information and details.</flux:text>
    </div>

    <div class="mx-auto max-w-2xl">
        <form wire:submit="save"
            class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="space-y-6">
                <flux:field>
                    <flux:label>Customer *</flux:label>
                    <flux:select wire:model="form.customer_id" placeholder="Select a customer">
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->full_name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="form.customer_id" />
                </flux:field>

                <div class="grid gap-6 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Device Type *</flux:label>
                        <flux:input wire:model="form.type" placeholder="e.g., Smartphone, Laptop" />
                        <flux:error name="form.type" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Brand *</flux:label>
                        <flux:input wire:model="form.brand" placeholder="e.g., Apple, Samsung" />
                        <flux:error name="form.brand" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Model *</flux:label>
                    <flux:input wire:model="form.model" placeholder="e.g., iPhone 15 Pro, MacBook Pro" />
                    <flux:error name="form.model" />
                </flux:field>

                <div class="grid gap-6 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Serial Number</flux:label>
                        <flux:input wire:model="form.serial_number" placeholder="Optional" />
                        <flux:error name="form.serial_number" />
                    </flux:field>

                    <flux:field>
                        <flux:label>IMEI</flux:label>
                        <flux:input wire:model="form.imei" placeholder="Optional" />
                        <flux:error name="form.imei" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Notes</flux:label>
                    <flux:textarea wire:model="form.notes" rows="3"
                        placeholder="Additional device information..." />
                    <flux:error name="form.notes" />
                </flux:field>

                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">Update Device</span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Updating...
                        </span>
                    </flux:button>
                    <flux:button href="{{ route('devices.show', $device) }}" variant="ghost">Cancel</flux:button>
                </div>
            </div>
        </form>
    </div>
</div>
