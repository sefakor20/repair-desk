<div class="max-w-3xl space-y-6">
    <!-- Header -->
    <div>
        <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
            <a href="{{ route('tickets.index') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-white">
                Tickets
            </a>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span>New Ticket</span>
        </div>
        <h1 class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">Create Ticket</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Create a new repair ticket</p>
    </div>

    <!-- Form -->
    <form wire:submit="save" class="space-y-6">
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="space-y-6">
                <!-- Customer Selection -->
                <div>
                    <label for="customer" class="block text-sm font-medium text-zinc-900 dark:text-white">
                        Customer <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="selectedCustomerId" id="customer"
                        class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white py-2.5 px-3 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-white dark:focus:ring-white">
                        <option value="">Select a customer</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->full_name }} ({{ $customer->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('form.customer_id')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Device Selection -->
                <div>
                    <label for="device" class="block text-sm font-medium text-zinc-900 dark:text-white">
                        Device <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="selectedDeviceId" id="device" @disabled(!$selectedCustomerId)
                        class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white py-2.5 px-3 text-sm text-zinc-900 disabled:cursor-not-allowed disabled:opacity-50 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-white dark:focus:ring-white">
                        <option value="">
                            {{ $selectedCustomerId ? 'Select a device' : 'Select a customer first' }}</option>
                        @foreach ($devices as $device)
                            <option value="{{ $device->id }}">
                                {{ $device->device_name }}
                                @if ($device->serial_number)
                                    - S/N: {{ $device->serial_number }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('form.device_id')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    @if ($selectedCustomerId && $devices->isEmpty())
                        <div class="mt-2 rounded-lg bg-yellow-50 p-3 dark:bg-yellow-900/20">
                            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                This customer has no registered devices.
                                <a href="{{ route('customers.show', $selectedCustomerId) }}" wire:navigate
                                    class="font-medium underline hover:no-underline">
                                    Add a device first
                                </a>
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Problem Description -->
                <div>
                    <label for="problem" class="block text-sm font-medium text-zinc-900 dark:text-white">
                        Problem Description <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="form.problem_description" id="problem" rows="4"
                        placeholder="Describe the issue with the device..."
                        class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white py-2.5 px-3 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500 dark:focus:border-white dark:focus:ring-white"></textarea>
                    @error('form.problem_description')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">Minimum 10 characters</p>
                </div>

                <!-- Initial Diagnosis -->
                <div>
                    <label for="diagnosis" class="block text-sm font-medium text-zinc-900 dark:text-white">
                        Initial Diagnosis (Optional)
                    </label>
                    <textarea wire:model="form.diagnosis" id="diagnosis" rows="3" placeholder="Initial assessment or diagnosis..."
                        class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white py-2.5 px-3 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500 dark:focus:border-white dark:focus:ring-white"></textarea>
                    @error('form.diagnosis')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror>
                </div>

                <!-- Priority -->
                <div>
                    <label for="priority" class="block text-sm font-medium text-zinc-900 dark:text-white">
                        Priority <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1.5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ($priorities as $priority)
                            <label
                                class="relative flex cursor-pointer rounded-lg border border-zinc-200 bg-white p-4 hover:bg-zinc-50 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700/50">
                                <input type="radio" wire:model="form.priority" value="{{ $priority->value }}"
                                    class="sr-only" />
                                <span class="flex flex-1 items-center">
                                    <span class="flex flex-col text-sm">
                                        <x-status-badge :status="$priority" />
                                    </span>
                                </span>
                                <svg class="h-5 w-5 text-zinc-900 dark:text-white"
                                    :class="{ 'hidden': @js($form['priority'] ?? '') !== '{{ $priority->value }}' }"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </label>
                        @endforeach
                    </div>
                    @error('form.priority')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Assign Technician -->
                <div>
                    <label for="technician" class="block text-sm font-medium text-zinc-900 dark:text-white">
                        Assign Technician (Optional)
                    </label>
                    <select wire:model="form.assigned_to" id="technician"
                        class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white py-2.5 px-3 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-white dark:focus:ring-white">
                        <option value="">Unassigned</option>
                        @foreach ($technicians as $technician)
                            <option value="{{ $technician->id }}">{{ $technician->name }}
                                ({{ $technician->role->label() }})
                            </option>
                        @endforeach
                    </select>
                    @error('form.assigned_to')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estimated Completion -->
                <div>
                    <label for="estimated_completion" class="block text-sm font-medium text-zinc-900 dark:text-white">
                        Estimated Completion Date (Optional)
                    </label>
                    <input type="date" wire:model="form.estimated_completion" id="estimated_completion"
                        min="{{ now()->addDay()->format('Y-m-d') }}"
                        class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white py-2.5 px-3 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-white dark:focus:ring-white">
                    @error('form.estimated_completion')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('tickets.index') }}" wire:navigate
                class="rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                Cancel
            </a>
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 dark:focus:ring-white">
                <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span wire:loading.remove wire:target="save">Create Ticket</span>
                <span wire:loading wire:target="save">Creating...</span>
            </button>
        </div>
    </form>
</div>
