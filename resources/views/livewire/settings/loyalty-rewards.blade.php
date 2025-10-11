<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Loyalty Rewards')" :subheading="__('Manage redeemable rewards for loyal customers')">
        @if (session('success'))
            <flux:callout variant="success" icon="check-circle" class="mb-6">
                {{ session('success') }}
            </flux:callout>
        @endif

        <div class="mb-6 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Create rewards customers can redeem using their loyalty points
                </p>
            </div>
            <flux:button wire:click="openModal" variant="primary" icon="plus">
                Add Reward
            </flux:button>
        </div>

        @if ($rewards->isEmpty())
            <div class="rounded-lg border-2 border-dashed border-gray-300 p-12 text-center dark:border-gray-700">
                <flux:icon.gift class="mx-auto mb-4 size-12 text-gray-400 dark:text-gray-600" />
                <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-gray-100">No rewards yet</h3>
                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    Create your first reward to incentivize customer loyalty
                </p>
                <flux:button wire:click="openModal" variant="primary" icon="plus">
                    Create First Reward
                </flux:button>
            </div>
        @else
            <div class="grid gap-4">
                @foreach ($rewards as $reward)
                    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <flux:icon.gift class="size-6 text-purple-500" />
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $reward->name }}</h3>
                                    <flux:badge :variant="$reward->is_active ? 'success' : 'muted'">
                                        {{ $reward->is_active ? 'Active' : 'Inactive' }}
                                    </flux:badge>
                                    <flux:badge variant="outline">{{ $reward->type->label() }}</flux:badge>
                                </div>

                                @if ($reward->description)
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $reward->description }}
                                    </p>
                                @endif

                                <div class="mt-4 grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
                                    <div>
                                        <div class="text-gray-500 dark:text-gray-400">Points Required</div>
                                        <div class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ number_format($reward->points_required) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-gray-500 dark:text-gray-400">Min Tier</div>
                                        <div class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $reward->minTier?->name ?? 'Any' }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-gray-500 dark:text-gray-400">Times Redeemed</div>
                                        <div class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $reward->times_redeemed }}
                                            @if ($reward->redemption_limit)
                                                / {{ $reward->redemption_limit }}
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-gray-500 dark:text-gray-400">Valid Period</div>
                                        <div class="font-medium text-gray-900 dark:text-gray-100">
                                            @if ($reward->valid_from && $reward->valid_until)
                                                {{ $reward->valid_from->format('M d') }} -
                                                {{ $reward->valid_until->format('M d, Y') }}
                                            @elseif($reward->valid_from)
                                                From {{ $reward->valid_from->format('M d, Y') }}
                                            @elseif($reward->valid_until)
                                                Until {{ $reward->valid_until->format('M d, Y') }}
                                            @else
                                                Always
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if ($reward->reward_value)
                                    <div class="mt-3">
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Reward Value:</div>
                                        <div class="mt-1">
                                            @if ($reward->type->value === 'discount')
                                                <flux:badge variant="success">
                                                    {{ $reward->reward_value['percentage'] }}% discount</flux:badge>
                                            @elseif($reward->type->value === 'free_product')
                                                <flux:badge variant="info">Product SKU:
                                                    {{ $reward->reward_value['sku'] ?? 'N/A' }}</flux:badge>
                                            @elseif($reward->type->value === 'free_service')
                                                <flux:badge variant="info">Service:
                                                    {{ $reward->reward_value['service_name'] ?? 'N/A' }}</flux:badge>
                                            @elseif($reward->type->value === 'voucher')
                                                <flux:badge variant="success">
                                                    ${{ number_format($reward->reward_value['amount'], 2) }} voucher
                                                </flux:badge>
                                            @elseif($reward->type->value === 'custom')
                                                <flux:badge variant="muted">
                                                    {{ Str::limit($reward->reward_value['instructions'] ?? '', 50) }}
                                                </flux:badge>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="ml-4 flex gap-2">
                                <flux:button wire:click="toggleActive('{{ $reward->id }}')" variant="ghost"
                                    size="sm" icon="{{ $reward->is_active ? 'eye-slash' : 'eye' }}">
                                </flux:button>
                                <flux:button wire:click="openModal('{{ $reward->id }}')" variant="ghost"
                                    size="sm" icon="pencil">
                                </flux:button>
                                <flux:button wire:click="delete('{{ $reward->id }}')"
                                    wire:confirm="Are you sure you want to delete this reward?" variant="ghost"
                                    size="sm" icon="trash">
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($rewards->hasPages())
                <div class="mt-6">
                    {{ $rewards->links() }}
                </div>
            @endif
        @endif

        <!-- Create/Edit Modal -->
        <flux:modal wire:model="showModal" class="max-w-3xl">
            <form wire:submit="save">
                <flux:heading>{{ $editingId ? 'Edit' : 'Create' }} Loyalty Reward</flux:heading>

                <div class="mt-6 space-y-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>Reward Name *</flux:label>
                            <flux:input wire:model="name" placeholder="e.g., 10% Off Next Purchase, Free Product" />
                            <flux:error name="name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Description</flux:label>
                            <flux:textarea wire:model="description" placeholder="What customers get with this reward"
                                rows="2" />
                            <flux:error name="description" />
                        </flux:field>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <flux:field>
                                <flux:label>Reward Type *</flux:label>
                                <flux:select wire:model.live="type">
                                    <option value="">Select type...</option>
                                    @foreach ($rewardTypes as $rewardType)
                                        <option value="{{ $rewardType['value'] }}">{{ $rewardType['label'] }}</option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="type" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Points Required *</flux:label>
                                <flux:input type="number" wire:model="points_required" placeholder="100"
                                    min="1" />
                                <flux:error name="points_required" />
                            </flux:field>
                        </div>
                    </div>

                    <!-- Type-Specific Fields -->
                    @if ($type)
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <h4 class="mb-3 font-semibold text-gray-900 dark:text-gray-100">Reward Configuration</h4>

                            @if ($type === 'discount')
                                <flux:field>
                                    <flux:label>Discount Percentage *</flux:label>
                                    <flux:input type="number" step="0.01" wire:model="discount_percentage"
                                        placeholder="10" min="0" max="100" />
                                    <flux:description>Percentage off the purchase (0-100%)</flux:description>
                                </flux:field>
                            @elseif($type === 'free_product')
                                <flux:field>
                                    <flux:label>Product SKU *</flux:label>
                                    <flux:input wire:model="product_sku" placeholder="PROD-123" />
                                    <flux:description>SKU of the free product</flux:description>
                                </flux:field>
                            @elseif($type === 'free_service')
                                <flux:field>
                                    <flux:label>Service Name *</flux:label>
                                    <flux:input wire:model="service_name"
                                        placeholder="Screen Repair, Battery Replacement" />
                                    <flux:description>Name of the free service</flux:description>
                                </flux:field>
                            @elseif($type === 'voucher')
                                <flux:field>
                                    <flux:label>Voucher Amount *</flux:label>
                                    <flux:input type="number" step="0.01" wire:model="voucher_amount"
                                        placeholder="25.00" min="0" />
                                    <flux:description>Dollar value of the voucher</flux:description>
                                </flux:field>
                            @elseif($type === 'custom')
                                <flux:field>
                                    <flux:label>Custom Instructions *</flux:label>
                                    <flux:textarea wire:model="custom_instructions"
                                        placeholder="Describe what the customer receives" rows="3" />
                                    <flux:description>Instructions for fulfilling this reward</flux:description>
                                </flux:field>
                            @endif
                        </div>
                    @endif

                    <!-- Requirements & Restrictions -->
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <h4 class="mb-3 font-semibold text-gray-900 dark:text-gray-100">Requirements & Restrictions
                        </h4>
                        <div class="space-y-4">
                            <flux:field>
                                <flux:label>Minimum Tier</flux:label>
                                <flux:select wire:model="min_tier_id">
                                    <option value="">Any tier (no restriction)</option>
                                    @foreach ($tiers as $tier)
                                        <option value="{{ $tier->id }}">{{ $tier->name }}</option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="min_tier_id" />
                                <flux:description>Only customers at or above this tier can redeem</flux:description>
                            </flux:field>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <flux:field>
                                    <flux:label>Valid From</flux:label>
                                    <flux:input type="date" wire:model="valid_from" />
                                    <flux:error name="valid_from" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>Valid Until</flux:label>
                                    <flux:input type="date" wire:model="valid_until" />
                                    <flux:error name="valid_until" />
                                </flux:field>
                            </div>

                            <flux:field>
                                <flux:label>Redemption Limit</flux:label>
                                <flux:input type="number" wire:model="redemption_limit"
                                    placeholder="Leave empty for unlimited" min="1" />
                                <flux:error name="redemption_limit" />
                                <flux:description>Maximum number of times this reward can be redeemed across all
                                    customers</flux:description>
                            </flux:field>
                        </div>
                    </div>

                    <!-- Status -->
                    <flux:field>
                        <div class="flex items-center gap-2">
                            <flux:checkbox wire:model="is_active" />
                            <flux:label>Active (customers can see and redeem this reward)</flux:label>
                        </div>
                    </flux:field>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <flux:button type="button" wire:click="closeModal" variant="ghost">Cancel</flux:button>
                    <flux:button type="submit" variant="primary" icon="check">
                        {{ $editingId ? 'Update' : 'Create' }} Reward
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    </x-settings.layout>
</section>
