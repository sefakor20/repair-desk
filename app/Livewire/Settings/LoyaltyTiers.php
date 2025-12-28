<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Models\LoyaltyTier;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class LoyaltyTiers extends Component
{
    public bool $showModal = false;

    public ?string $editingId = null;

    public string $name = '';

    public string $description = '';

    public int $min_points = 0;

    public float $points_multiplier = 1.0;

    public float $discount_percentage = 0;

    public string $color = '#CD7F32';

    public int $priority = 1;

    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorize('accessSettings', auth()->user());
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $tiers = LoyaltyTier::orderBy('min_points', 'asc')->get();

        return view('livewire.settings.loyalty-tiers', [
            'tiers' => $tiers,
        ]);
    }

    public function openModal(?string $tierId = null): void
    {
        $this->resetForm();
        $this->editingId = $tierId;

        if ($tierId) {
            $tier = LoyaltyTier::findOrFail($tierId);
            $this->name = $tier->name;
            $this->description = $tier->description ?? '';
            $this->min_points = $tier->min_points;
            $this->points_multiplier = (float) $tier->points_multiplier;
            $this->discount_percentage = (float) $tier->discount_percentage;
            $this->color = $tier->color ?? '#CD7F32';
            $this->priority = $tier->priority;
            $this->is_active = $tier->is_active;
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
            'min_points' => ['required', 'integer', 'min:0'],
            'points_multiplier' => ['required', 'numeric', 'min:1', 'max:10'],
            'discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'color' => ['nullable', 'string', 'max:7'],
            'priority' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        if ($this->editingId) {
            $tier = LoyaltyTier::findOrFail($this->editingId);
            $tier->update($validated);
            $message = 'Loyalty tier updated successfully.';
        } else {
            LoyaltyTier::create($validated);
            $message = 'Loyalty tier created successfully.';
        }

        $this->closeModal();
        session()->flash('success', $message);
    }

    public function delete(string $tierId): void
    {
        $tier = LoyaltyTier::findOrFail($tierId);

        // Check if any customers are using this tier
        if ($tier->accounts()->exists()) {
            session()->flash('error', 'Cannot delete tier that has active customer accounts.');
            return;
        }

        $tier->delete();
        session()->flash('success', 'Loyalty tier deleted successfully.');
    }

    public function toggleActive(string $tierId): void
    {
        $tier = LoyaltyTier::findOrFail($tierId);
        $tier->update(['is_active' => ! $tier->is_active]);

        session()->flash('success', 'Tier status updated successfully.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->description = '';
        $this->min_points = 0;
        $this->points_multiplier = 1.0;
        $this->discount_percentage = 0;
        $this->color = '#CD7F32';
        $this->priority = 1;
        $this->is_active = true;
        $this->resetValidation();
    }
}
