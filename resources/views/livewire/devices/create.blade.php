<div>
    <div class="mb-6">
        <flux:heading size="xl">Register Device</flux:heading>
        <flux:text class="mt-1">Register a new device for a customer to track repair history.</flux:text>
    </div>

    <div class="max-w-4xl">
        <form wire:submit="save"
            class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="space-y-8">
                {{-- Customer Selection --}}
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Customer Information</h3>
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

                {{-- Device Details --}}
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Device Details</h3>
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
                                <flux:input wire:model="form.color" placeholder="e.g., Black, Silver"
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
                                <flux:input wire:model="form.imei" placeholder="For phones and tablets"
                                    :invalid="$errors->has('form.imei')" />
                                <flux:error name="form.imei" />
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label>Password/PIN</flux:label>
                            <flux:input wire:model="form.password_pin" type="password"
                                placeholder="Device unlock code (stored securely)"
                                :invalid="$errors->has('form.password_pin')" />
                            <flux:error name="form.password_pin" />
                            <flux:description>Optional: Store device password/PIN for repair access</flux:description>
                        </flux:field>
                    </div>
                </div>

                {{-- Condition Assessment --}}
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Condition Assessment</h3>
                    <div class="space-y-6">
                        <flux:field>
                            <flux:label>Device Condition</flux:label>
                            <flux:select wire:model="form.condition" placeholder="Select condition"
                                :invalid="$errors->has('form.condition')">
                                <option value="excellent">Excellent - Like new, no visible wear</option>
                                <option value="good">Good - Minor wear, fully functional</option>
                                <option value="fair">Fair - Noticeable wear, functional</option>
                                <option value="poor">Poor - Heavy wear, may have issues</option>
                                <option value="damaged">Damaged - Significant damage</option>
                            </flux:select>
                            <flux:error name="form.condition" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Condition Notes</flux:label>
                            <flux:textarea wire:model="form.condition_notes" rows="3"
                                placeholder="Describe scratches, dents, screen condition, etc..."
                                :invalid="$errors->has('form.condition_notes')" />
                            <flux:error name="form.condition_notes" />
                        </flux:field>
                    </div>
                </div>

                {{-- Warranty Information --}}
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Warranty Information</h3>
                    <div class="space-y-6">
                        <div class="grid gap-6 sm:grid-cols-2">
                            <flux:field>
                                <flux:label>Purchase Date</flux:label>
                                <flux:input wire:model="form.purchase_date" type="date"
                                    :invalid="$errors->has('form.purchase_date')" />
                                <flux:error name="form.purchase_date" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Warranty Expiry</flux:label>
                                <flux:input wire:model="form.warranty_expiry" type="date"
                                    :invalid="$errors->has('form.warranty_expiry')" />
                                <flux:error name="form.warranty_expiry" />
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label>Warranty Provider</flux:label>
                            <flux:input wire:model="form.warranty_provider" placeholder="e.g., AppleCare, Samsung Care+"
                                :invalid="$errors->has('form.warranty_provider')" />
                            <flux:error name="form.warranty_provider" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Warranty Notes</flux:label>
                            <flux:textarea wire:model="form.warranty_notes" rows="2"
                                placeholder="Coverage details, exclusions, etc..."
                                :invalid="$errors->has('form.warranty_notes')" />
                            <flux:error name="form.warranty_notes" />
                        </flux:field>
                    </div>
                </div>

                {{-- Additional Notes --}}
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Additional Notes</h3>
                    <flux:field>
                        <flux:label>Notes</flux:label>
                        <flux:textarea wire:model="form.notes" rows="3"
                            placeholder="Any additional information about this device..."
                            :invalid="$errors->has('form.notes')" />
                        <flux:error name="form.notes" />
                    </flux:field>
                </div>

                <div class="flex gap-2 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">Register Device</span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Registering...
                        </span>
                    </flux:button>
                    <flux:button href="{{ route('devices.index') }}" variant="ghost">Cancel</flux:button>
                </div>
            </div>
        </form>
    </div>
</div>
