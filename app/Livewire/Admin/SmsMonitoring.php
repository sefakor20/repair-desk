<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\SmsDeliveryLog;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class SmsMonitoring extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', SmsDeliveryLog::class);

        // Default to last 30 days
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'statusFilter']);
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function export(): void
    {
        Gate::authorize('viewAny', SmsDeliveryLog::class);

        // This will trigger a download
        $this->dispatch('export-sms-logs');
    }

    public function render(): View
    {
        $query = SmsDeliveryLog::query()
            ->with('notifiable')
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('phone', 'like', "%{$this->search}%")
                        ->orWhere('message', 'like', "%{$this->search}%")
                        ->orWhere('notification_type', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest();

        $logs = $query->paginate(20);

        // Statistics
        $stats = [
            'total' => SmsDeliveryLog::whereBetween('created_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])->count(),
            'sent' => SmsDeliveryLog::where('status', 'sent')->whereBetween('created_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])->count(),
            'failed' => SmsDeliveryLog::where('status', 'failed')->whereBetween('created_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])->count(),
            'pending' => SmsDeliveryLog::where('status', 'pending')->whereBetween('created_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])->count(),
        ];

        // Calculate success rate
        $successRate = $stats['total'] > 0 ? round(($stats['sent'] / $stats['total']) * 100, 1) : 0;

        return view('livewire.admin.sms-monitoring', [
            'logs' => $logs,
            'stats' => $stats,
            'successRate' => $successRate,
        ])->layout('components.layouts.app');
    }
}
