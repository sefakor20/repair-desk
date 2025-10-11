<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Loyalty Tiers')" :subheading="__('Manage customer loyalty tier levels and benefits')">
        @if (session('success'))
            <flux:callout variant="success" icon="check-circle" class="mb-6">
                {{ session('success') }}
            </flux:callout>
        @endif

        @if (session('error'))
            <flux:callout variant="danger" icon="x-circle" class="mb-6">
                {{ session('error') }}
            </flux:callout>
        @endif

        <div class="mb-6 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Define tier levels customers progress through as they earn loyalty points
                </p>
            </div>
            <flux:button wire:click="openModal" variant="primary" icon="plus">
                Add Tier
            </flux:button>
        </div>

        @if ($tiers->isEmpty())
            <div class="rounded-lg border-2 border-dashed border-gray-300 p-12 text-center dark:border-gray-700">
                <flux:icon.trophy class="mx-auto mb-4 size-12 text-gray-400 dark:text-gray-600" />
                <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-gray-100">No loyalty tiers yet</h3>
                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    Get started by creating your first loyalty tier
                </p>
                <flux:button wire:click="openModal" variant="primary" icon="plus">
                    Create First Tier
                </flux:button>
            </div>
        @else
            <div
                class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Tier
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Min Points
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Benefits
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Customers
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Status
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        @foreach ($tiers as $tier)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="size-10 rounded-lg flex items-center justify-center"
                                            style="background-color: {{ $tier->color }}20; border: 2px solid {{ $tier->color }}">
                                            <flux:icon.trophy class="size-5" style="color: {{ $tier->color }}" />
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $tier->name }}</div>
                                            @if ($tier->description)
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ Str::limit($tier->description, 40) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    {{ number_format($tier->min_points) }} pts
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                            <flux:icon.sparkles class="size-4 text-yellow-500" />
                                            <span>{{ $tier->points_multiplier }}x points multiplier</span>
                                        </div>
                                        @if ($tier->discount_percentage > 0)
                                            <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                                <flux:icon.tag class="size-4 text-green-500" />
                                                <span>{{ $tier->discount_percentage }}% discount</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    <flux:badge variant="outline">
                                        {{ $tier->accounts()->count() }} customers
                                    </flux:badge>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if ($tier->is_active)
                                        <flux:badge variant="success">Active</flux:badge>
                                    @else
                                        <flux:badge variant="muted">Inactive</flux:badge>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <flux:button wire:click="openModal('{{ $tier->id }}')" variant="ghost"
                                            icon="pencil" size="sm">
                                            Edit
                                        </flux:button>
                                        <flux:button wire:click="toggleActive('{{ $tier->id }}')"
                                            wire:confirm="Are you sure you want to {{ $tier->is_active ? 'deactivate' : 'activate' }} this tier?"
                                            variant="ghost" size="sm">
                                            {{ $tier->is_active ? 'Deactivate' : 'Activate' }}
                                        </flux:button>
                                        <flux:button wire:click="delete('{{ $tier->id }}')"
                                            wire:confirm="Are you sure you want to delete this tier? This action cannot be undone."
                                            variant="ghost" icon="trash" size="sm">
                                            Delete
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Create/Edit Modal -->
        <flux:modal wire:model="showModal" class="max-w-2xl">
            <form wire:submit="save">
                <flux:heading>{{ $editingId ? 'Edit' : 'Create' }} Loyalty Tier</flux:heading>

                <div class="mt-6 space-y-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>Tier Name *</flux:label>
                            <flux:input wire:model="name" placeholder="e.g., Bronze, Silver, Gold" />
                            <flux:error name="name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Description</flux:label>
                            <flux:textarea wire:model="description" placeholder="Brief description of this tier"
                                rows="2" />
                            <flux:error name="description" />
                        </flux:field>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <flux:field>
                                <flux:label>Minimum Points *</flux:label>
                                <flux:input type="number" wire:model="min_points" placeholder="0" min="0" />
                                <flux:error name="min_points" />
                                <flux:description>Points required to reach this tier</flux:description>
                            </flux:field>

                            <flux:field>
                                <flux:label>Priority *</flux:label>
                                <flux:input type="number" wire:model="priority" placeholder="1" min="1" />
                                <flux:error name="priority" />
                                <flux:description>Display order (lower = higher priority)</flux:description>
                            </flux:field>
                        </div>
                    </div>

                    <!-- Benefits -->
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <h4 class="mb-3 font-semibold text-gray-900 dark:text-gray-100">Tier Benefits</h4>
                        <div class="space-y-4">
                            <flux:field>
                                <flux:label>Points Multiplier *</flux:label>
                                <flux:input type="number" step="0.01" wire:model="points_multiplier"
                                    placeholder="1.0" min="1" max="10" />
                                <flux:error name="points_multiplier" />
                                <flux:description>Multiply earned points by this value (e.g., 1.5 = 50% bonus)
                                </flux:description>
                            </flux:field>

                            <flux:field>
                                <flux:label>Discount Percentage *</flux:label>
                                <flux:input type="number" step="0.01" wire:model="discount_percentage"
                                    placeholder="0" min="0" max="100" />
                                <flux:error name="discount_percentage" />
                                <flux:description>Discount applied on purchases (0-100%)</flux:description>
                            </flux:field>

                            <flux:field>
                                <flux:label>Tier Color</flux:label>
                                <div class="flex items-center gap-3">
                                    <input type="color" wire:model.live="color"
                                        class="size-10 cursor-pointer rounded border border-gray-300 dark:border-gray-600" />
                                    <flux:input wire:model="color" placeholder="#CD7F32" />
                                </div>
                                <flux:error name="color" />
                                <flux:description>Visual identifier for this tier</flux:description>
                            </flux:field>
                        </div>
                    </div>

                    <!-- Status -->
                    <flux:field>
                        <div class="flex items-center gap-2">
                            <flux:checkbox wire:model="is_active" />
                            <flux:label>Active (customers can reach this tier)</flux:label>
                        </div>
                    </flux:field>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <flux:button type="button" wire:click="closeModal" variant="ghost">Cancel</flux:button>
                    <flux:button type="submit" variant="primary" icon="check">
                        {{ $editingId ? 'Update' : 'Create' }} Tier
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    </x-settings.layout>
</section>
