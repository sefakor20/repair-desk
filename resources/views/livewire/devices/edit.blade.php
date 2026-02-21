<div>
    <div class="mb-6">
        <flux:heading size="xl">Edit Device</flux:heading>
        <flux:text class="mt-1">Update device information and details.</flux:text>
    </div>

    <div class="max-w-4xl">
        <form wire:submit="save"
            class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="space-y-8">
                <!-- Customer Information -->
                <div>
                    <h3 class="mb-4 text-sm font-semibold text-zinc-900 dark:text-white">Customer Information</h3>
                    <flux:field>
                        <flux:label>Customer *</flux:label>
                        <flux:select wire:model="form.customer_id" placeholder="Select a customer"
                            :invalid="$errors->has('form.customer_id')">
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->full_name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="form.customer_id" />
                    </flux:field>
                </div>

                <!-- Device Details -->
                <div>
                    <h3 class="mb-4 text-sm font-semibold text-zinc-900 dark:text-white">Device Details</h3>
                    <div class="space-y-6">
                        {{-- Device Type Dropdown --}}
                        <flux:field>
                            <flux:label>Device Type *</flux:label>
                            <flux:select wire:model.live="device_type" :invalid="$errors->has('device_type')">
                                @foreach ($deviceCategories as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="device_type" />
                        </flux:field>

                        {{-- Brand Selection --}}
                        <flux:field>
                            <flux:label>Brand *</flux:label>
                            <flux:select wire:model.live="brand_id" :invalid="$errors->has('brand_id')">
                                <option value="">Select a brand or enter custom below</option>
                                @foreach ($this->brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="brand_id" />
                        </flux:field>

                        @if (!$brand_id)
                            <flux:field>
                                <flux:label>Custom Brand</flux:label>
                                <flux:input wire:model="form.brand" placeholder="Enter brand name (if not in list above)"
                                    :invalid="$errors->has('form.brand')" />
                                <flux:error name="form.brand" />
                                <flux:description>Only fill this if the brand is not available in the dropdown</flux:description>
                            </flux:field>
                        @endif

                        {{-- Model Selection --}}
                        @if ($brand_id)
                            <flux:field>
                                <flux:label>Model *</flux:label>
                                <flux:select wire:model.live="model_id" :invalid="$errors->has('model_id')">
                                    <option value="">Select a model or enter custom below</option>
                                    @foreach ($this->models as $model)
                                        <option value="{{ $model->id }}">{{ $model->name }}</option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="model_id" />
                            </flux:field>
                        @endif

                        @if (!$model_id)
                            <flux:field>
                                <flux:label>Custom Model</flux:label>
                                <flux:input wire:model="form.model" placeholder="Enter model name (if not in list above)"
                                    :invalid="$errors->has('form.model')" />
                                <flux:error name="form.model" />
                                <flux:description>Only fill this if the model is not available in the dropdown</flux:description>
                            </flux:field>
                        @endif

                        <div class="grid gap-6 sm:grid-cols-2">
                            <flux:field>
                                <flux:label>Color</flux:label>
                                <flux:input wire:model="form.color" placeholder="e.g., Space Gray, Midnight"
                                    :invalid="$errors->has('form.color')" />
                                <flux:error name="form.color" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Storage Capacity</flux:label>
                                <flux:input wire:model="form.storage_capacity" placeholder="e.g., 256GB, 1TB"
                                    :invalid="$errors->has('form.storage_capacity')" />
                                <flux:error name="form.storage_capacity" />
                            </flux:field>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <flux:field>
                                <flux:label>Serial Number</flux:label>
                                <flux:input wire:model="form.serial_number" placeholder="Optional"
                                    :invalid="$errors->has('form.serial_number')" />
                                <flux:error name="form.serial_number" />
                            </flux:field>

                            <flux:field>
                                <flux:label>IMEI</flux:label>
                                <flux:input wire:model="form.imei" placeholder="Optional"
                                    :invalid="$errors->has('form.imei')" />
                                <flux:error name="form.imei" />
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label>Device Password/PIN</flux:label>
                            <flux:input type="password" wire:model="form.password_pin"
                                placeholder="Leave blank to keep current password"
                                :invalid="$errors->has('form.password_pin')" />
                            <flux:error name="form.password_pin" />
                            <flux:text class="mt-1 text-xs">Optional: Update device password/PIN for repair access
                            </flux:text>
                        </flux:field>
                    </div>
                </div>

                <!-- Condition Assessment -->
                <div>
                    <h3 class="mb-4 text-sm font-semibold text-zinc-900 dark:text-white">Condition Assessment</h3>
                    <div class="space-y-6">
                        <flux:field>
                            <flux:label>Device Condition</flux:label>
                            <flux:select wire:model="form.condition" placeholder="Select condition"
                                :invalid="$errors->has('form.condition')">
                                <option value="excellent">Excellent - Like new, no signs of wear</option>
                                <option value="good">Good - Minor wear, fully functional</option>
                                <option value="fair">Fair - Moderate wear, some issues</option>
                                <option value="poor">Poor - Heavy wear, multiple issues</option>
                                <option value="damaged">Damaged - Significant damage, limited functionality</option>
                            </flux:select>
                            <flux:error name="form.condition" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Condition Notes</flux:label>
                            <flux:textarea wire:model="form.condition_notes" rows="3"
                                placeholder="Describe any scratches, dents, screen damage, or other issues..."
                                :invalid="$errors->has('form.condition_notes')" />
                            <flux:error name="form.condition_notes" />
                        </flux:field>
                    </div>
                </div>

                <!-- Warranty Information -->
                <div>
                    <h3 class="mb-4 text-sm font-semibold text-zinc-900 dark:text-white">Warranty Information</h3>
                    <div class="space-y-6">
                        <div class="grid gap-6 sm:grid-cols-2">
                            <flux:field>
                                <flux:label>Purchase Date</flux:label>
                                <flux:input type="date" wire:model="form.purchase_date"
                                    :invalid="$errors->has('form.purchase_date')" />
                                <flux:error name="form.purchase_date" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Warranty Expiry</flux:label>
                                <flux:input type="date" wire:model="form.warranty_expiry"
                                    :invalid="$errors->has('form.warranty_expiry')" />
                                <flux:error name="form.warranty_expiry" />
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label>Warranty Provider</flux:label>
                            <flux:input wire:model="form.warranty_provider"
                                placeholder="e.g., AppleCare, Samsung Care, Extended Warranty"
                                :invalid="$errors->has('form.warranty_provider')" />
                            <flux:error name="form.warranty_provider" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Warranty Notes</flux:label>
                            <flux:textarea wire:model="form.warranty_notes" rows="3"
                                placeholder="Warranty terms, coverage details, claim information..."
                                :invalid="$errors->has('form.warranty_notes')" />
                            <flux:error name="form.warranty_notes" />
                        </flux:field>
                    </div>
                </div>

                <!-- Additional Notes -->
                <div>
                    <h3 class="mb-4 text-sm font-semibold text-zinc-900 dark:text-white">Additional Notes</h3>
                    <flux:field>
                        <flux:label>Notes</flux:label>
                        <flux:textarea wire:model="form.notes" rows="3"
                            placeholder="Additional device information, accessories included, special instructions..."
                            :invalid="$errors->has('form.notes')" />
                        <flux:error name="form.notes" />
                    </flux:field>
                </div>

                <div class="border-t border-zinc-200 pt-6 dark:border-zinc-700">
                    <div class="flex gap-2">
                        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">Update Device</span>
                            <span wire:loading wire:target="save" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
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
            </div>
        </form>
    </div>
</div>
