<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" class="mb-2">{{ __('Edit Invoice') }}</flux:heading>
                <flux:text>{{ $invoice->invoice_number }}</flux:text>
            </div>
            <flux:button :href="route('invoices.show', $invoice)" variant="ghost" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
        </div>
    </div>

    <div class="mx-auto max-w-2xl">
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <form wire:submit="update">
                <div class="space-y-6">
                    {{-- Invoice Info --}}
                    <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900">
                        <flux:text class="font-medium">{{ __('Ticket:') }} {{ $invoice->ticket->ticket_number }}
                        </flux:text>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Customer:') }}
                            {{ $invoice->customer->name }}</flux:text>
                    </div>

                    {{-- Subtotal --}}
                    <flux:field>
                        <flux:label>{{ __('Subtotal') }}</flux:label>
                        <flux:input wire:model="subtotal" type="number" step="0.01" min="0" required
                            placeholder="0.00" />
                        @error('subtotal')
                            <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                        @enderror
                    </flux:field>

                    {{-- Tax Rate --}}
                    <flux:field>
                        <flux:label>{{ __('Tax Rate (%)') }}</flux:label>
                        <flux:input wire:model="taxRate" type="number" step="0.01" min="0" max="100"
                            placeholder="0.00" />
                        @error('taxRate')
                            <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                        @enderror
                    </flux:field>

                    {{-- Discount --}}
                    <flux:field>
                        <flux:label>{{ __('Discount') }}</flux:label>
                        <flux:input wire:model="discount" type="number" step="0.01" min="0"
                            placeholder="0.00" />
                        @error('discount')
                            <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                        @enderror
                    </flux:field>

                    {{-- Status --}}
                    <flux:field>
                        <flux:label>{{ __('Status') }}</flux:label>
                        <flux:select wire:model="status" required>
                            <option value="pending">{{ __('Pending') }}</option>
                            <option value="paid">{{ __('Paid') }}</option>
                            <option value="cancelled">{{ __('Cancelled') }}</option>
                        </flux:select>
                        @error('status')
                            <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                        @enderror
                    </flux:field>

                    {{-- Notes --}}
                    <flux:field>
                        <flux:label>{{ __('Notes') }}</flux:label>
                        <flux:textarea wire:model="notes" rows="3" placeholder="{{ __('Additional notes...') }}"
                            maxlength="1000" />
                        @error('notes')
                            <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                        @enderror
                    </flux:field>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-3">
                        <flux:button type="button" variant="ghost" :href="route('invoices.show', $invoice)"
                            wire:navigate>
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button type="submit">
                            {{ __('Update Invoice') }}
                        </flux:button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
