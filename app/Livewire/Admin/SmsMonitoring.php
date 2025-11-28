<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\SmsDeliveryLog;
use App\Services\SmsService;
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

    public function export()
    {
        Gate::authorize('viewAny', SmsDeliveryLog::class);

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
            ->latest()
            ->get();

        $filename = 'sms-logs-' . now()->format('Y-m-d-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, ['Date', 'Time', 'Phone', 'Message', 'Type', 'Status', 'Customer', 'Error', 'External ID', 'Cost', 'Segments']);

            foreach ($query as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d'),
                    $log->created_at->format('H:i:s'),
                    $log->phone_number,
                    $log->message,
                    $log->notification_type,
                    $log->status,
                    $log->notifiable?->name ?? 'N/A',
                    $log->error_message ?? '',
                    $log->external_id ?? '',
                    $log->cost ? '$' . number_format($log->cost, 4) : '$0.0000',
                    $log->segments,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function retryMessage(string $logId, SmsService $smsService): void
    {
        Gate::authorize('viewAny', SmsDeliveryLog::class);

        $log = SmsDeliveryLog::findOrFail($logId);

        if ($log->status !== 'failed') {
            $this->dispatch('error', message: 'Only failed messages can be retried.');

            return;
        }

        $success = $smsService->retrySms($log);

        if ($success) {
            $this->dispatch('success', message: 'SMS retried successfully!');
        } else {
            $this->dispatch('error', message: 'Failed to retry SMS. Check the logs for details.');
        }
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
            'total_cost' => SmsDeliveryLog::whereBetween('created_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])->sum('cost'),
            'total_segments' => SmsDeliveryLog::whereBetween('created_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])->sum('segments'),
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
