<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\SmsCampaign;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class SmsCampaigns extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public bool $showDeleteConfirm = false;

    public ?string $campaignToDelete = null;

    public bool $showAnalytics = false;

    protected $queryString = ['search', 'statusFilter'];

    public function mount(): void
    {
        Gate::authorize('viewAny', SmsCampaign::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(string $campaignId): void
    {
        $campaign = SmsCampaign::findOrFail($campaignId);
        Gate::authorize('delete', $campaign);

        $this->campaignToDelete = $campaignId;
        $this->showDeleteConfirm = true;
    }

    public function deleteCampaign(): void
    {
        if (! $this->campaignToDelete) {
            return;
        }

        $campaign = SmsCampaign::findOrFail($this->campaignToDelete);
        Gate::authorize('delete', $campaign);

        $campaign->delete();

        $this->showDeleteConfirm = false;
        $this->campaignToDelete = null;

        session()->flash('success', 'Campaign deleted successfully.');
    }

    public function cancelCampaign(string $campaignId): void
    {
        $campaign = SmsCampaign::findOrFail($campaignId);
        Gate::authorize('cancel', $campaign);

        $campaign->markAsCancelled();

        session()->flash('success', 'Campaign cancelled successfully.');
    }

    public function toggleAnalytics(): void
    {
        $this->showAnalytics = !$this->showAnalytics;
    }

    public function getAnalyticsProperty(): array
    {
        $totalCampaigns = SmsCampaign::count();
        $totalSent = SmsCampaign::sum('sent_count');
        $totalFailed = SmsCampaign::sum('failed_count');
        $totalCost = SmsCampaign::sum('actual_cost');
        $avgDeliveryRate = $totalSent > 0 ? round(($totalSent / ($totalSent + $totalFailed)) * 100, 1) : 0;

        // Recent campaigns (last 30 days)
        $recentCampaigns = SmsCampaign::where('created_at', '>=', now()->subDays(30))->count();
        $recentSent = SmsCampaign::where('created_at', '>=', now()->subDays(30))->sum('sent_count');

        return [
            'total_campaigns' => $totalCampaigns,
            'total_sent' => $totalSent,
            'total_failed' => $totalFailed,
            'total_cost' => $totalCost,
            'avg_delivery_rate' => $avgDeliveryRate,
            'recent_campaigns' => $recentCampaigns,
            'recent_sent' => $recentSent,
            'active_campaigns' => SmsCampaign::active()->count(),
        ];
    }

    #[Layout('components.layouts.app')]
    public function render(): View
    {
        $query = SmsCampaign::query()
            ->with('creator')
            ->when($this->search, function ($q): void {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('message', 'like', "%{$this->search}%");
            })
            ->when($this->statusFilter !== 'all', function ($q): void {
                $q->where('status', $this->statusFilter);
            })
            ->latest();

        return view('livewire.admin.sms-campaigns', [
            'campaigns' => $query->paginate(15),
        ]);
    }
}
