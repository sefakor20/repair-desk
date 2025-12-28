<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Enums\LoyaltyRewardType;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyTier;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class LoyaltyRewards extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?string $editingId = null;

    public string $name = '';

    public string $description = '';

    public string $type = '';

    public int $points_required = 0;

    public array $reward_value = [];

    public ?string $min_tier_id = null;

    public ?string $valid_from = null;

    public ?string $valid_until = null;

    public ?int $redemption_limit = null;

    public bool $is_active = true;

    // Type-specific fields
    public float $discount_percentage = 0;

    public ?string $product_sku = null;

    public ?string $service_name = null;

    public float $voucher_amount = 0;

    public string $custom_instructions = '';

    public function mount(): void
    {
        $this->authorize('accessSettings', auth()->user());
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $rewards = LoyaltyReward::with('minTier')
            ->orderBy('is_active', 'desc')
            ->orderBy('points_required', 'asc')
            ->paginate(10);

        $tiers = LoyaltyTier::active()->orderedByPriority()->get();

        $rewardTypes = collect(LoyaltyRewardType::cases())->map(fn($case): array => [
            'value' => $case->value,
            'label' => $case->label(),
        ]);

        return view('livewire.settings.loyalty-rewards', [
            'rewards' => $rewards,
            'tiers' => $tiers,
            'rewardTypes' => $rewardTypes,
        ]);
    }

    public function openModal(?string $rewardId = null): void
    {
        $this->resetForm();
        $this->editingId = $rewardId;

        if ($rewardId) {
            $reward = LoyaltyReward::findOrFail($rewardId);
            $this->name = $reward->name;
            $this->description = $reward->description ?? '';
            $this->type = $reward->type->value;
            $this->points_required = $reward->points_required;
            $this->reward_value = $reward->reward_value ?? [];
            $this->min_tier_id = $reward->min_tier_id;
            $this->valid_from = $reward->valid_from?->format('Y-m-d');
            $this->valid_until = $reward->valid_until?->format('Y-m-d');
            $this->redemption_limit = $reward->redemption_limit;
            $this->is_active = $reward->is_active;

            // Populate type-specific fields
            $this->populateTypeSpecificFields();
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'in:discount,free_product,free_service,voucher,custom'],
            'points_required' => ['required', 'integer', 'min:1'],
            'min_tier_id' => ['nullable', 'exists:loyalty_tiers,id'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after:valid_from'],
            'redemption_limit' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        // Build reward_value based on type
        $validated['reward_value'] = $this->buildRewardValue();

        // Convert empty strings to null
        $validated['min_tier_id'] = $validated['min_tier_id'] ?: null;
        $validated['valid_from'] = $validated['valid_from'] ?: null;
        $validated['valid_until'] = $validated['valid_until'] ?: null;
        $validated['redemption_limit'] = $validated['redemption_limit'] ?: null;

        if ($this->editingId) {
            $reward = LoyaltyReward::findOrFail($this->editingId);
            $reward->update($validated);
            $message = 'Loyalty reward updated successfully.';
        } else {
            LoyaltyReward::create($validated);
            $message = 'Loyalty reward created successfully.';
        }

        $this->closeModal();
        session()->flash('success', $message);
    }

    public function delete(string $rewardId): void
    {
        $reward = LoyaltyReward::findOrFail($rewardId);
        $reward->delete();

        session()->flash('success', 'Loyalty reward deleted successfully.');
    }

    public function toggleActive(string $rewardId): void
    {
        $reward = LoyaltyReward::findOrFail($rewardId);
        $reward->update(['is_active' => ! $reward->is_active]);

        session()->flash('success', 'Reward status updated successfully.');
    }

    protected function buildRewardValue(): array
    {
        return match ($this->type) {
            'discount' => [
                'percentage' => $this->discount_percentage,
            ],
            'free_product' => [
                'sku' => $this->product_sku,
            ],
            'free_service' => [
                'service_name' => $this->service_name,
            ],
            'voucher' => [
                'amount' => $this->voucher_amount,
            ],
            'custom' => [
                'instructions' => $this->custom_instructions,
            ],
            default => [],
        };
    }

    protected function populateTypeSpecificFields(): void
    {
        $value = $this->reward_value;

        match ($this->type) {
            'discount' => $this->discount_percentage = $value['percentage'] ?? 0,
            'free_product' => $this->product_sku = $value['sku'] ?? null,
            'free_service' => $this->service_name = $value['service_name'] ?? null,
            'voucher' => $this->voucher_amount = $value['amount'] ?? 0,
            'custom' => $this->custom_instructions = $value['instructions'] ?? '',
            default => null,
        };
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->description = '';
        $this->type = '';
        $this->points_required = 0;
        $this->reward_value = [];
        $this->min_tier_id = null;
        $this->valid_from = null;
        $this->valid_until = null;
        $this->redemption_limit = null;
        $this->is_active = true;

        // Reset type-specific fields
        $this->discount_percentage = 0;
        $this->product_sku = null;
        $this->service_name = null;
        $this->voucher_amount = 0;
        $this->custom_instructions = '';

        $this->resetValidation();
    }
}
