<?php

declare(strict_types=1);

namespace App\Livewire\Portal;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\{Attributes\Layout, Attributes\Url, Component, WithPagination};

#[Layout('components.layouts.portal-fullpage')]
class NotificationHistory extends Component
{
    use WithPagination;

    public Customer $customer;

    #[Url]
    public string $filter = 'all';

    #[Url]
    public string $search = '';

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filter = 'all';
        $this->search = '';
        $this->resetPage();
    }

    public function getSmsLogsProperty(): LengthAwarePaginator
    {
        $query = $this->customer->smsDeliveryLogs()
            ->latest('created_at');

        // Apply status filter
        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }

        // Apply search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('message', 'like', '%' . $this->search . '%')
                    ->orWhere('notification_type', 'like', '%' . $this->search . '%');
            });
        }

        return $query->paginate(20);
    }

    public function getStatsProperty(): array
    {
        $logs = $this->customer->smsDeliveryLogs;

        return [
            'total' => $logs->count(),
            'sent' => $logs->where('status', 'sent')->count(),
            'failed' => $logs->where('status', 'failed')->count(),
            'pending' => $logs->where('status', 'pending')->count(),
        ];
    }

    public function render(): View
    {
        return view('livewire.portal.notification-history', [
            'smsLogs' => $this->smsLogs,
            'stats' => $this->stats,
        ]);
    }
}
