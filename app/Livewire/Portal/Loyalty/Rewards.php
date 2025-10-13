<?php

declare(strict_types=1);

namespace App\Livewire\Portal\Loyalty;

use App\Mail\LoyaltyRewardRedeemed;
use App\Models\{Customer, CustomerLoyaltyAccount, LoyaltyReward};
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Livewire\{Attributes\On, Component};
use Livewire\WithPagination;
use Exception;

class Rewards extends Component
{
    use WithPagination;

    public Customer $customer;

    public CustomerLoyaltyAccount $account;

    public ?LoyaltyReward $selectedReward = null;

    public bool $showRedemptionModal = false;

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;

        // Ensure customer has a portal access token
        if (! $customer->portal_access_token) {
            $customer->generatePortalAccessToken();
        }
        $this->account = $customer->loyaltyAccount ?? throw new Exception('Loyalty account not found');
    }

    public function selectReward(string $rewardId): void
    {
        $this->selectedReward = LoyaltyReward::findOrFail($rewardId);
        $this->showRedemptionModal = true;
    }

    public function closeModal(): void
    {
        $this->showRedemptionModal = false;
        $this->selectedReward = null;
    }

    #[On('reward-redeemed')]
    public function refreshAccount(): void
    {
        $this->account->refresh();
    }

    public function redeemReward(): void
    {
        if (! $this->selectedReward) {
            return;
        }

        if (! $this->selectedReward->canBeRedeemedBy($this->account)) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'You are not eligible to redeem this reward.',
            ]);

            return;
        }

        try {
            $transaction = $this->selectedReward->redeem($this->account);

            Mail::to($this->customer->email)->send(
                new LoyaltyRewardRedeemed($this->customer, $this->selectedReward, $transaction),
            );

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Reward redeemed successfully!',
            ]);

            $this->dispatch('reward-redeemed');
            $this->closeModal();
        } catch (Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function render(): View
    {
        $rewards = LoyaltyReward::available()
            ->where(function ($query) {
                $query->whereNull('min_tier_id')
                    ->orWhere(function ($q) {
                        if ($this->account->loyaltyTier) {
                            $q->whereHas('minTier', function ($tierQuery) {
                                $tierQuery->where('priority', '<=', $this->account->loyaltyTier->priority);
                            });
                        }
                    });
            })
            ->with('minTier')
            ->paginate(12);

        return view('livewire.portal.loyalty.rewards', [
            'rewards' => $rewards,
        ]);
    }
}
