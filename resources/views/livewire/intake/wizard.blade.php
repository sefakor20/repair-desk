<div>
    <div class="mb-6">
        <flux:heading size="lg">Device Intake Wizard</flux:heading>
        <flux:text variant="muted">
            Create a new repair ticket with customer, device, and assessment information
        </flux:text>
    </div>

    <!-- Step Indicator -->
    <div class="mb-8">
        <div class="flex items-center justify-between max-w-3xl mx-auto">
            @foreach (range(1, $totalSteps) as $step)
                <div class="flex items-center {{ $step < $totalSteps ? 'flex-1' : '' }}">
                    <button type="button" wire:click="goToStep({{ $step }})"
                        class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-colors
                            {{ $currentStep >= $step ? 'bg-blue-600 border-blue-600 text-white' : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-400' }}
                            {{ $step <= $currentStep ? 'cursor-pointer hover:bg-blue-700 hover:border-blue-700' : 'cursor-not-allowed' }}">
                        {{ $step }}
                    </button>

                    @if ($step < $totalSteps)
                        <div
                            class="flex-1 h-1 mx-2 {{ $currentStep > $step ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600' }}">
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Step Labels -->
        <div class="flex items-center justify-between max-w-3xl mx-auto mt-2">
            <flux:text variant="muted" class="text-xs text-center w-24">Customer</flux:text>
            <flux:text variant="muted" class="text-xs text-center w-24">Device</flux:text>
            <flux:text variant="muted" class="text-xs text-center w-24">Warranty</flux:text>
            <flux:text variant="muted" class="text-xs text-center w-24">Assessment</flux:text>
            <flux:text variant="muted" class="text-xs text-center w-24">Ticket</flux:text>
        </div>
    </div>

    <!-- Step Content -->
    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 min-h-[400px]">
            <!-- Step 1: Customer Information -->
            @if ($currentStep === 1)
                <div class="space-y-6">
                    <div>
                        <flux:heading size="md" class="mb-4">Customer Information</flux:heading>
                    </div>

                    <!-- Create New Customer Toggle -->
                    <div>
                        <flux:checkbox wire:model.live="createNewCustomer" label="Create New Customer" />
                    </div>

                    @if ($createNewCustomer)
                        <!-- New Customer Form -->
                        <div class="space-y-4">
                            <flux:input wire:model="customer_name" label="Customer Name" required />
                            @error('customer_name')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror

                            <flux:input wire:model="customer_phone" type="tel" label="Phone Number" required />
                            @error('customer_phone')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror

                            <flux:input wire:model="customer_email" type="email" label="Email (Optional)" />
                            @error('customer_email')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </div>
                    @else
                        <!-- Existing Customer Selection -->
                        <div>
                            <flux:select wire:model.live="customer_id" label="Select Customer" required>
                                <option value="">Choose a customer...</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} -
                                        {{ $customer->phone }}</option>
                                @endforeach
                            </flux:select>
                            @error('customer_id')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror

                            @if ($selectedCustomer)
                                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <flux:text variant="muted" class="text-sm mb-2">Selected Customer</flux:text>
                                    <div class="space-y-1">
                                        <flux:text><strong>Name:</strong> {{ $selectedCustomer->name }}</flux:text>
                                        @if ($selectedCustomer->email)
                                            <flux:text><strong>Email:</strong> {{ $selectedCustomer->email }}</flux:text>
                                        @endif
                                        @if ($selectedCustomer->phone)
                                            <flux:text><strong>Phone:</strong> {{ $selectedCustomer->phone }}</flux:text>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            <!-- Step 2: Device Information -->
            @if ($currentStep === 2)
                <div class="space-y-6">
                    <div>
                        <flux:heading size="md" class="mb-4">Device Information</flux:heading>
                    </div>

                    <!-- Device Type -->
                    <div>
                        <flux:select wire:model.live="device_type" label="Device Type" required>
                            @foreach ($deviceCategories as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                        @error('device_type')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </div>

                    <!-- Brand Selection -->
                    <div>
                        <flux:select wire:model.live="brand_id" label="Brand (Optional)">
                            <option value="">Select a brand or enter custom</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </flux:select>
                        @if (!$brand_id)
                            <flux:input wire:model="brand" label="Custom Brand" placeholder="Enter brand name"
                                class="mt-2" />
                        @endif
                    </div>

                    <!-- Model Selection -->
                    <div>
                        @if ($brand_id)
                            <flux:select wire:model="model_id" label="Model (Optional)">
                                <option value="">Select a model or enter custom</option>
                                @foreach ($models as $model)
                                    <option value="{{ $model->id }}">{{ $model->name }}</option>
                                @endforeach
                            </flux:select>
                        @endif

                        @if (!$model_id)
                            <flux:input wire:model="model" label="Custom Model" placeholder="Enter model name"
                                class="mt-2" />
                        @endif
                    </div>

                    <!-- IMEI/Serial -->
                    <div>
                        <flux:input wire:model="imei" label="IMEI / Serial Number (Optional)"
                            placeholder="Enter device IMEI or serial number" />
                    </div>

                    <!-- Device Password -->
                    <div>
                        <flux:input wire:model="device_password" type="password" label="Device Password (Optional)"
                            placeholder="Lock screen password or PIN" />
                        <flux:description>Needed to access the device for diagnostics</flux:description>
                    </div>

                    <!-- Cosmetic Condition -->
                    <div>
                        <flux:select wire:model="cosmetic_condition" label="Cosmetic Condition">
                            <option value="">Select condition</option>
                            <option value="excellent">Excellent - Like new</option>
                            <option value="good">Good - Minor wear</option>
                            <option value="fair">Fair - Visible scratches/dents</option>
                            <option value="poor">Poor - Significant damage</option>
                        </flux:select>
                    </div>
                </div>
            @endif

            <!-- Step 3: Warranty Information -->
            @if ($currentStep === 3)
                <div class="space-y-6">
                    <div>
                        <flux:heading size="md" class="mb-4">Warranty Information</flux:heading>
                        <flux:text variant="muted">Optional - Provide warranty details if applicable</flux:text>
                    </div>

                    <!-- Purchase Date -->
                    <div>
                        <flux:input wire:model="purchase_date" type="date" label="Purchase Date (Optional)" />
                    </div>

                    <!-- Under Warranty -->
                    <div>
                        <flux:checkbox wire:model.live="under_warranty" label="Device is under warranty" />
                    </div>

                    @if ($under_warranty)
                        <!-- Warranty Details -->
                        <div class="space-y-4">
                            <flux:input wire:model="warranty_expiry_date" type="date" label="Warranty Expiry Date" />

                            <flux:input wire:model="warranty_provider" label="Warranty Provider"
                                placeholder="e.g., Manufacturer, AppleCare, Best Buy" />
                        </div>
                    @endif
                </div>
            @endif

            <!-- Step 4: Device Assessment -->
            @if ($currentStep === 4)
                <div class="space-y-6">
                    <div>
                        <flux:heading size="md" class="mb-4">Device Assessment (Check-in)</flux:heading>
                        <flux:text variant="muted">
                            Document the device condition at intake - Optional but recommended
                        </flux:text>
                    </div>

                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <livewire:device-assessment-form :device="null" :ticket="null"
                            :type="\App\Enums\AssessmentType::CheckIn" :key="'assessment-step'" />
                    </div>
                </div>
            @endif

            <!-- Step 5: Ticket Details -->
            @if ($currentStep === 5)
                <div class="space-y-6">
                    <div>
                        <flux:heading size="md" class="mb-4">Ticket Details</flux:heading>
                    </div>

                    <!-- Reported Issue -->
                    <div>
                        <flux:textarea wire:model="reported_issue" label="Reported Issue" required
                            placeholder="Describe the problem reported by the customer..." rows="4" />
                        @error('reported_issue')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </div>

                    <!-- Diagnosed Faults -->
                    <div>
                        <flux:text variant="muted" class="mb-2">Diagnosed Faults (Select all that apply)</flux:text>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-64 overflow-y-auto p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            @forelse($commonFaults as $fault)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="diagnosed_faults"
                                        value="{{ $fault->id }}"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <flux:text>{{ $fault->name }}</flux:text>
                                </label>
                            @empty
                                <flux:text variant="muted">No common faults available</flux:text>
                            @endforelse
                        </div>
                    </div>

                    <!-- Priority -->
                    <div>
                        <flux:select wire:model="priority" label="Priority" required>
                            @foreach ($priorities as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                        @error('priority')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </div>

                    <!-- Estimated Completion -->
                    <div>
                        <flux:input wire:model="estimated_completion" type="date"
                            label="Estimated Completion (Optional)" />
                    </div>
                </div>
            @endif
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between mt-6">
            <div>
                @if ($currentStep > 1)
                    <flux:button type="button" variant="ghost" wire:click="previousStep">
                        Previous
                    </flux:button>
                @endif
            </div>

            <div class="flex gap-3">
                <flux:button type="button" variant="ghost" :href="route('tickets.index')">
                    Cancel
                </flux:button>

                @if ($currentStep < $totalSteps)
                    <flux:button type="button" variant="primary" wire:click="nextStep">
                        Next
                    </flux:button>
                @else
                    <flux:button type="button" variant="primary" wire:click="submit">
                        Create Ticket
                    </flux:button>
                @endif
            </div>
        </div>
    </div>
</div>
