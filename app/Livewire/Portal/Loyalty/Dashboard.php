<?php

declare(strict_types=1);

namespace App\Livewire\Portal\Loyalty;

use App\Models\{Customer, CustomerLoyaltyAccount, LoyaltyReward, LoyaltyTier};
use Illuminate\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public Customer $customer;

    public CustomerLoyaltyAccount $account;

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;

        // Ensure customer has a portal access token
        if (! $customer->portal_access_token) {
            $customer->generatePortalAccessToken();
        }

        // Ensure customer has a loyalty account
        $this->account = $customer->loyaltyAccount()->firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'total_points' => 0,
                'lifetime_points' => 0,
                'enrolled_at' => now(),
            ],
        );
    }

    public function render(): View
    {
        $nextTier = null;
        $progress = 0;

        if ($this->account->loyaltyTier) {
            // Get next tier
            $nextTier = LoyaltyTier::active()
                ->where('min_points', '>', $this->account->total_points)
                ->orderBy('min_points', 'asc')
                ->first();

            if ($nextTier) {
                $currentTierPoints = $this->account->loyaltyTier->min_points;
                $pointsNeeded = $nextTier->min_points - $currentTierPoints;
                $pointsEarned = $this->account->total_points - $currentTierPoints;
                $progress = $pointsNeeded > 0 ? min(100, ($pointsEarned / $pointsNeeded) * 100) : 100;
            }
        } else {
            // No tier yet, show progress to first tier
            $nextTier = LoyaltyTier::active()
                ->orderBy('min_points', 'asc')
                ->first();

            if ($nextTier && $nextTier->min_points > 0) {
                $progress = min(100, ($this->account->total_points / $nextTier->min_points) * 100);
            }
        }

        return view('livewire.portal.loyalty.dashboard', [
            'nextTier' => $nextTier,
            'progress' => $progress,
            'availableRewards' => LoyaltyReward::available()
                ->where('points_required', '<=', $this->account->total_points)
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
                ->limit(3)
                ->get(),
            'recentTransactions' => $this->account->transactions()
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
