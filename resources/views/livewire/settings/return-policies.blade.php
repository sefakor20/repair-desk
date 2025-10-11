<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold">Return Policies</h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Configure return and refund policies for your POS sales
                </p>
            </div>
            <flux:button wire:click="openModal" variant="primary" icon="plus">
                New Policy
            </flux:button>
        </div>
    </div>

    @if (session('success'))
        <flux:callout variant="success" icon="check-circle" class="mb-6">
            {{ session('success') }}
        </flux:callout>
    @endif

    <div class="grid gap-4">
        @forelse ($policies as $policy)
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-semibold">{{ $policy->name }}</h3>
                            <flux:badge :variant="$policy->is_active ? 'success' : 'muted'">
                                {{ $policy->is_active ? 'Active' : 'Inactive' }}
                            </flux:badge>
                        </div>

                        @if ($policy->description)
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $policy->description }}
                            </p>
                        @endif

                        <div class="mt-4 grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
                            <div>
                                <div class="text-zinc-500 dark:text-zinc-400">Return Window</div>
                                <div class="font-medium">{{ $policy->return_window_days }} days</div>
                            </div>
                            <div>
                                <div class="text-zinc-500 dark:text-zinc-400">Restocking Fee</div>
                                <div class="font-medium">{{ $policy->restocking_fee_percentage }}%</div>
                            </div>
                            <div>
                                <div class="text-zinc-500 dark:text-zinc-400">Receipt Required</div>
                                <div class="font-medium">{{ $policy->requires_receipt ? 'Yes' : 'No' }}</div>
                            </div>
                            <div>
                                <div class="text-zinc-500 dark:text-zinc-400">Approval Required</div>
                                <div class="font-medium">{{ $policy->requires_approval ? 'Yes' : 'No' }}</div>
                            </div>
                        </div>

                        @if ($policy->allowed_conditions)
                            <div class="mt-3">
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">Allowed Conditions:</div>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach ($policy->getAllowedConditionsLabels() as $condition)
                                        <flux:badge variant="muted" size="sm">{{ $condition }}</flux:badge>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="ml-4 flex gap-2">
                        <flux:button wire:click="toggleActive('{{ $policy->id }}')" variant="ghost" size="sm"
                            icon="{{ $policy->is_active ? 'eye-slash' : 'eye' }}">
                        </flux:button>
                        <flux:button wire:click="openModal('{{ $policy->id }}')" variant="ghost" size="sm"
                            icon="pencil">
                        </flux:button>
                        <flux:button wire:click="delete('{{ $policy->id }}')"
                            wire:confirm="Are you sure you want to delete this return policy?" variant="ghost"
                            size="sm" icon="trash">
                        </flux:button>
                    </div>
                </div>
            </div>
        @empty
            <div
                class="rounded-lg border-2 border-dashed border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-800">
                <flux:icon.document-text class="mx-auto h-12 w-12 text-zinc-400" />
                <h3 class="mt-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">No return policies</h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Get started by creating your first return
                    policy.</p>
                <div class="mt-6">
                    <flux:button wire:click="openModal" variant="primary" icon="plus">
                        Create Policy
                    </flux:button>
                </div>
            </div>
        @endforelse
    </div>

    @if ($policies->hasPages())
        <div class="mt-6">
            {{ $policies->links() }}
        </div>
    @endif

    {{-- Modal --}}
    <flux:modal wire:model="showModal" class="max-w-3xl">
        <form wire:submit="save">
            <flux:heading>{{ $editingId ? 'Edit' : 'Create' }} Return Policy</flux:heading>

            <div class="mt-6 space-y-6">
                {{-- Basic Information --}}
                <div class="grid gap-6 md:grid-cols-2">
                    <flux:field label="Policy Name" required>
                        <flux:input wire:model="name" placeholder="Standard Return Policy" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field label="Return Window (Days)" required>
                        <flux:input wire:model="return_window_days" type="number" min="1" max="365" />
                        <flux:error name="return_window_days" />
                    </flux:field>
                </div>

                <flux:field label="Description">
                    <flux:textarea wire:model="description" rows="2"
                        placeholder="Brief description of this policy..." />
                    <flux:error name="description" />
                </flux:field>

                {{-- Fees --}}
                <div class="grid gap-6 md:grid-cols-2">
                    <flux:field label="Restocking Fee (%)" required>
                        <flux:input wire:model="restocking_fee_percentage" type="number" step="0.01" min="0"
                            max="100" />
                        <flux:error name="restocking_fee_percentage" />
                    </flux:field>

                    <flux:field label="Minimum Restocking Fee" required>
                        <flux:input wire:model="minimum_restocking_fee" type="number" step="0.01" min="0" />
                        <flux:error name="minimum_restocking_fee" />
                    </flux:field>
                </div>

                {{-- Requirements --}}
                <div class="space-y-3">
                    <flux:heading size="sm">Requirements</flux:heading>

                    <flux:checkbox wire:model="requires_receipt" label="Receipt Required" />
                    <flux:checkbox wire:model="requires_original_packaging" label="Original Packaging Required" />
                    <flux:checkbox wire:model="requires_approval" label="Manager Approval Required" />
                    <flux:checkbox wire:model="refund_shipping" label="Refund Shipping Costs" />
                    <flux:checkbox wire:model="is_active" label="Active" />
                </div>

                {{-- Allowed Conditions --}}
                <flux:field label="Allowed Product Conditions" required>
                    <div class="space-y-2">
                        @foreach ($conditions as $condition)
                            <flux:checkbox wire:model="allowed_conditions" :value="$condition['value']"
                                :label="$condition['label']" />
                        @endforeach
                    </div>
                    <flux:error name="allowed_conditions" />
                </flux:field>

                {{-- Terms --}}
                <flux:field label="Additional Terms & Conditions">
                    <flux:textarea wire:model="terms" rows="4"
                        placeholder="Enter any additional terms and conditions..." />
                    <flux:error name="terms" />
                </flux:field>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <flux:button type="button" wire:click="closeModal" variant="ghost">Cancel</flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $editingId ? 'Update' : 'Create' }} Policy
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
