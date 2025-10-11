<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Shop Settings')" :subheading="__('Manage your shop profile and business information')">
        @if (session('success'))
            <flux:callout variant="success" icon="check-circle" class="mb-6">
                {{ session('success') }}
            </flux:callout>
        @endif

        <form wire:submit="save" class="space-y-6">
            <!-- Business Information -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Business Information</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Basic information about your repair shop</p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <flux:field>
                            <flux:label>Shop Name *</flux:label>
                            <flux:input wire:model="shop_name" placeholder="Enter shop name" />
                            <flux:error name="shop_name" />
                        </flux:field>
                    </div>

                    <div class="sm:col-span-2">
                        <flux:field>
                            <flux:label>Address</flux:label>
                            <flux:input wire:model="address" placeholder="Street address" />
                            <flux:error name="address" />
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>City</flux:label>
                            <flux:input wire:model="city" placeholder="City" />
                            <flux:error name="city" />
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>State/Province</flux:label>
                            <flux:input wire:model="state" placeholder="State or Province" />
                            <flux:error name="state" />
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>ZIP/Postal Code</flux:label>
                            <flux:input wire:model="zip" placeholder="ZIP or Postal Code" />
                            <flux:error name="zip" />
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>Country *</flux:label>
                            <flux:input wire:model="country" placeholder="Country" />
                            <flux:error name="country" />
                        </flux:field>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Contact Information</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">How customers can reach you</p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <flux:field>
                            <flux:label>Phone</flux:label>
                            <flux:input type="tel" wire:model="phone" placeholder="(555) 123-4567" />
                            <flux:error name="phone" />
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>Email</flux:label>
                            <flux:input type="email" wire:model="email" placeholder="shop@example.com" />
                            <flux:error name="email" />
                        </flux:field>
                    </div>

                    <div class="sm:col-span-2">
                        <flux:field>
                            <flux:label>Website</flux:label>
                            <flux:input type="url" wire:model="website" placeholder="https://example.com" />
                            <flux:error name="website" />
                        </flux:field>
                    </div>
                </div>
            </div>

            <!-- Business Configuration -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Business Configuration</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Tax and currency settings</p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <flux:field>
                            <flux:label>Default Tax Rate (%) *</flux:label>
                            <flux:input type="number" step="0.01" min="0" max="100"
                                wire:model="tax_rate" placeholder="0.00" />
                            <flux:error name="tax_rate" />
                            <flux:description>Enter as percentage (e.g., 8.5 for 8.5%)</flux:description>
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>Currency *</flux:label>
                            <flux:select wire:model="currency">
                                <option value="USD">USD - US Dollar</option>
                                <option value="EUR">EUR - Euro</option>
                                <option value="GBP">GBP - British Pound</option>
                                <option value="CAD">CAD - Canadian Dollar</option>
                                <option value="AUD">AUD - Australian Dollar</option>
                                <option value="JPY">JPY - Japanese Yen</option>
                                <option value="INR">INR - Indian Rupee</option>
                            </flux:select>
                            <flux:error name="currency" />
                        </flux:field>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <flux:button type="submit" variant="primary" icon="check">
                    Save Settings
                </flux:button>
            </div>
        </form>
    </x-settings.layout>
</section>
