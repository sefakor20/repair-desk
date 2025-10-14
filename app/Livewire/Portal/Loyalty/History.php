<?php

declare(strict_types=1);

namespace App\Livewire\Portal\Loyalty;

use App\Models\{Customer, CustomerLoyaltyAccount};
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\{Attributes\Url, Component};
use Livewire\WithPagination;
use Exception;

#[Layout('components.layouts.portal-fullpage')]
class History extends Component
{
    use WithPagination;

    public Customer $customer;

    public CustomerLoyaltyAccount $account;

    #[Url(as: 'type')]
    public string $filterType = 'all';

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;

        // Ensure customer has a portal access token
        if (! $customer->portal_access_token) {
            $customer->generatePortalAccessToken();
        }
        $this->account = $customer->loyaltyAccount ?? throw new Exception('Loyalty account not found');
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filterType = 'all';
        $this->resetPage();
    }

    public function render(): View
    {
        $transactions = $this->account->transactions()
            ->when($this->filterType !== 'all', function ($query) {
                $query->where('type', $this->filterType);
            })
            ->latest()
            ->paginate(20);

        return view('livewire.portal.loyalty.history', [
            'transactions' => $transactions,
        ]);
    }
}
