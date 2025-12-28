<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Enums\ReturnCondition;
use App\Models\ReturnPolicy;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ReturnPolicies extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public ?string $editingId = null;

    public string $name = '';
    public string $description = '';
    public bool $is_active = true;
    public int $return_window_days = 30;
    public bool $requires_receipt = true;
    public bool $requires_original_packaging = false;
    public bool $requires_approval = false;
    public float $restocking_fee_percentage = 0;
    public float $minimum_restocking_fee = 0;
    public bool $refund_shipping = false;
    public array $allowed_conditions = [];
    public array $excluded_categories = [];
    public string $terms = '';

    public function mount(): void
    {
        $this->authorize('accessSettings', auth()->user());
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $policies = ReturnPolicy::orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $conditions = collect(ReturnCondition::cases())->map(fn($case): array => [
            'value' => $case->value,
            'label' => $case->label(),
        ]);

        return view('livewire.settings.return-policies', [
            'policies' => $policies,
            'conditions' => $conditions,
        ]);
    }

    public function openModal(?string $policyId = null): void
    {
        $this->resetForm();
        $this->editingId = $policyId;

        if ($policyId) {
            $policy = ReturnPolicy::findOrFail($policyId);
            $this->name = $policy->name;
            $this->description = $policy->description ?? '';
            $this->is_active = $policy->is_active;
            $this->return_window_days = $policy->return_window_days;
            $this->requires_receipt = $policy->requires_receipt;
            $this->requires_original_packaging = $policy->requires_original_packaging;
            $this->requires_approval = $policy->requires_approval;
            $this->restocking_fee_percentage = (float) $policy->restocking_fee_percentage;
            $this->minimum_restocking_fee = (float) $policy->minimum_restocking_fee;
            $this->refund_shipping = $policy->refund_shipping;
            $this->allowed_conditions = $policy->allowed_conditions ?? [];
            $this->excluded_categories = $policy->excluded_categories ?? [];
            $this->terms = $policy->terms ?? '';
        } else {
            // Set default allowed conditions for new policies
            $this->allowed_conditions = [
                ReturnCondition::New->value,
                ReturnCondition::Opened->value,
            ];
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
            'is_active' => ['boolean'],
            'return_window_days' => ['required', 'integer', 'min:1', 'max:365'],
            'requires_receipt' => ['boolean'],
            'requires_original_packaging' => ['boolean'],
            'requires_approval' => ['boolean'],
            'restocking_fee_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'minimum_restocking_fee' => ['required', 'numeric', 'min:0'],
            'refund_shipping' => ['boolean'],
            'allowed_conditions' => ['required', 'array', 'min:1'],
            'allowed_conditions.*' => ['string', 'in:new,opened,used,damaged'],
            'excluded_categories' => ['nullable', 'array'],
            'terms' => ['nullable', 'string'],
        ]);

        if ($this->editingId) {
            $policy = ReturnPolicy::findOrFail($this->editingId);
            $policy->update($validated);
            $message = 'Return policy updated successfully.';
        } else {
            ReturnPolicy::create($validated);
            $message = 'Return policy created successfully.';
        }

        $this->closeModal();
        session()->flash('success', $message);
    }

    public function delete(string $policyId): void
    {
        $policy = ReturnPolicy::findOrFail($policyId);
        $policy->delete();

        session()->flash('success', 'Return policy deleted successfully.');
    }

    public function toggleActive(string $policyId): void
    {
        $policy = ReturnPolicy::findOrFail($policyId);
        $policy->update(['is_active' => ! $policy->is_active]);

        session()->flash('success', 'Policy status updated successfully.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->description = '';
        $this->is_active = true;
        $this->return_window_days = 30;
        $this->requires_receipt = true;
        $this->requires_original_packaging = false;
        $this->requires_approval = false;
        $this->restocking_fee_percentage = 0;
        $this->minimum_restocking_fee = 0;
        $this->refund_shipping = false;
        $this->allowed_conditions = [];
        $this->excluded_categories = [];
        $this->terms = '';
        $this->resetValidation();
    }
}
