<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" class="mb-2">{{ $invoice->invoice_number }}</flux:heading>
                <flux:text>{{ __('Invoice Details') }}</flux:text>
            </div>
            <div class="flex gap-2">
                @can('processPayment', $invoice)
                    @if ($invoice->balance_due > 0)
                        <flux:button type="button" wire:click="openPaymentModal">
                            <div class="flex items-center justify-center gap-2">
                                <flux:icon.plus class="size-5" />
                                <span>{{ __('Record Payment') }}</span>
                            </div>
                        </flux:button>
                    @endif
                @endcan
                @can('update', $invoice)
                    <flux:button :href="route('invoices.edit', $invoice)" variant="ghost" wire:navigate>
                        {{ __('Edit') }}
                    </flux:button>
                @endcan
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Invoice Info --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">{{ __('Invoice Information') }}</flux:heading>
            <div class="space-y-3">
                <div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</flux:text>
                    <div class="mt-1">
                        <flux:badge :color="$invoice->status->color()" size="sm">
                            {{ $invoice->status->label() }}
                        </flux:badge>
                    </div>
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Customer') }}</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $invoice->customer?->name }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Ticket') }}</flux:text>
                    <a href="{{ route('tickets.show', $invoice->ticket) }}"
                        class="mt-1 block font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                        wire:navigate>
                        {{ $invoice->ticket->ticket_number }}
                    </a>
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Device') }}</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $invoice->ticket->device?->name }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Invoice Date') }}</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $invoice->created_at->format('M d, Y') }}</flux:text>
                </div>
                @if ($invoice->notes)
                    <div>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Notes') }}</flux:text>
                        <flux:text class="mt-1">{{ $invoice->notes }}</flux:text>
                    </div>
                @endif
            </div>
        </div>

        {{-- Financial Summary --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">{{ __('Financial Summary') }}</flux:heading>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <flux:text>{{ __('Subtotal') }}</flux:text>
                    <flux:text class="font-medium">{{ format_currency($invoice->subtotal) }}</flux:text>
                </div>
                @if ($invoice->discount > 0)
                    <div class="flex justify-between text-red-600 dark:text-red-400">
                        <flux:text>{{ __('Discount') }}</flux:text>
                        <flux:text class="font-medium">-{{ format_currency($invoice->discount) }}</flux:text>
                    </div>
                @endif
                @if ($invoice->tax_rate > 0)
                    <div class="flex justify-between">
                        <flux:text>{{ __('Tax') }} ({{ number_format($invoice->tax_rate, 2) }}%)</flux:text>
                        <flux:text class="font-medium">{{ format_currency($invoice->tax_amount) }}</flux:text>
                    </div>
                @endif
                <div class="border-t border-zinc-200 pt-3 dark:border-zinc-700">
                    <div class="flex justify-between">
                        <flux:text class="text-lg font-semibold">{{ __('Total') }}</flux:text>
                        <flux:text class="text-lg font-semibold">{{ format_currency($invoice->total) }}</flux:text>
                    </div>
                </div>
                <div class="flex justify-between text-green-600 dark:text-green-400">
                    <flux:text class="font-medium">{{ __('Total Paid') }}</flux:text>
                    <flux:text class="font-medium">{{ format_currency($invoice->total_paid) }}</flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:text class="text-lg font-semibold">{{ __('Balance Due') }}</flux:text>
                    <flux:text class="text-lg font-semibold"
                        :class="$invoice->balance_due > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                        {{ format_currency($invoice->balance_due) }}
                    </flux:text>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment History --}}
    @if ($invoice->payments->isNotEmpty())
        <div class="mt-6 rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">{{ __('Payment History') }}</flux:heading>
            <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="w-full">
                    <thead class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                {{ __('Date') }}
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                {{ __('Amount') }}
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                {{ __('Method') }}
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                {{ __('Processed By') }}
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                {{ __('Reference') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($invoice->payments as $payment)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                    {{ $payment->payment_date->format('M d, Y H:i') }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-4 py-3 text-sm font-medium text-green-600 dark:text-green-400">
                                    {{ format_currency($payment->amount) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $payment->payment_method->label() }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $payment->processedBy->name }}
                                </td>
                                <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $payment->transaction_reference ?? '-' }}
                                    @if ($payment->notes)
                                        <p class="mt-1 text-xs">{{ $payment->notes }}</p>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Record Payment Modal --}}
    <flux:modal name="record-payment" :show="$showPaymentModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Record Payment') }}</flux:heading>
                <flux:subheading class="mt-2">
                    {{ __('Balance Due: $:amount', ['amount' => number_format($invoice->balance_due, 2)]) }}
                </flux:subheading>
            </div>

            <form wire:submit="recordPayment">
                <div class="space-y-4">
                    <flux:field>
                        <flux:label>{{ __('Amount') }}</flux:label>
                        <flux:input wire:model="amount" type="number" step="0.01" min="0.01"
                            :max="$invoice->balance_due" required />
                        @error('amount')
                            <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Payment Method') }}</flux:label>
                        <flux:select wire:model="paymentMethod" required>
                            <option value="">{{ __('Select method') }}</option>
                            <option value="cash">{{ __('Cash') }}</option>
                            <option value="card">{{ __('Card') }}</option>
                            <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                        </flux:select>
                        @error('paymentMethod')
                            <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Transaction Reference') }}</flux:label>
                        <flux:input wire:model="transactionReference" maxlength="255" />
                        @error('transactionReference')
                            <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                        @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Notes') }}</flux:label>
                        <flux:textarea wire:model="paymentNotes" rows="2" maxlength="500" />
                        @error('paymentNotes')
                            <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                        @enderror
                    </flux:field>

                    <div class="flex justify-end gap-2">
                        <flux:button type="button" variant="ghost" wire:click="closePaymentModal">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button type="submit">
                            {{ __('Record Payment') }}
                        </flux:button>
                    </div>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
