<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\SmsDeliveryLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class SmsReports extends Component
{
    public string $dateFrom = '';

    public string $dateTo = '';

    public string $groupBy = 'day'; // day, week, month

    public string $notificationType = 'all';

    public function mount(): void
    {
        Gate::authorize('viewAny', SmsDeliveryLog::class);

        // Default to current month
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function export()
    {
        Gate::authorize('viewAny', SmsDeliveryLog::class);

        $stats = $this->getDetailedStats();
        $costByType = $this->getCostByNotificationType();
        $costByStatus = $this->getCostByStatus();

        $filename = 'sms-cost-report-' . now()->format('Y-m-d-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($stats, $costByType, $costByStatus) {
            $file = fopen('php://output', 'w');

            // Summary section
            fputcsv($file, ['SMS Cost Report']);
            fputcsv($file, ['Generated', now()->format('Y-m-d H:i:s')]);
            fputcsv($file, ['Period', $this->dateFrom . ' to ' . $this->dateTo]);
            fputcsv($file, []);

            // Overall stats
            fputcsv($file, ['Overall Statistics']);
            fputcsv($file, ['Total Messages', number_format($stats['total_messages'])]);
            fputcsv($file, ['Total Cost', format_currency($stats['total_cost'] ?? 0)]);
            fputcsv($file, ['Total Segments', number_format($stats['total_segments'])]);
            fputcsv($file, ['Average Cost per Message', format_currency($stats['avg_cost_per_message'] ?? 0)]);
            fputcsv($file, ['Success Rate', $stats['success_rate'] . '%']);
            fputcsv($file, []);

            // Cost by type
            fputcsv($file, ['Cost by Notification Type']);
            fputcsv($file, ['Type', 'Messages', 'Cost', 'Segments']);
            foreach ($costByType as $type) {
                fputcsv($file, [
                    class_basename($type->notification_type ?? 'N/A'),
                    $type->total_messages,
                    format_currency($type->total_cost ?? 0),
                    $type->total_segments,
                ]);
            }
            fputcsv($file, []);

            // Cost by status
            fputcsv($file, ['Cost by Status']);
            fputcsv($file, ['Status', 'Messages', 'Cost', 'Segments']);
            foreach ($costByStatus as $status) {
                fputcsv($file, [
                    ucfirst($status->status),
                    $status->total_messages,
                    format_currency($status->total_cost ?? 0),
                    $status->total_segments,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    #[Layout('components.layouts.app')]
    public function render(): View
    {
        $stats = $this->getDetailedStats();
        $costOverTime = $this->getCostOverTime();
        $costByType = $this->getCostByNotificationType();
        $costByStatus = $this->getCostByStatus();
        $topCostDays = $this->getTopCostDays();

        return view('livewire.admin.sms-reports', [
            'stats' => $stats,
            'costOverTime' => $costOverTime,
            'costByType' => $costByType,
            'costByStatus' => $costByStatus,
            'topCostDays' => $topCostDays,
        ]);
    }

    private function getDetailedStats(): array
    {
        $query = SmsDeliveryLog::whereBetween('created_at', [$this->dateFrom, $this->dateTo . ' 23:59:59']);

        if ($this->notificationType !== 'all') {
            $query->where('notification_type', $this->notificationType);
        }

        $totalMessages = $query->count();
        $totalCost = $query->sum('cost') ?? 0;
        $totalSegments = $query->sum('segments') ?? 0;
        $sentMessages = (clone $query)->where('status', 'sent')->count();

        return [
            'total_messages' => $totalMessages,
            'total_cost' => $totalCost,
            'total_segments' => $totalSegments,
            'avg_cost_per_message' => $totalMessages > 0 ? $totalCost / $totalMessages : 0,
            'success_rate' => $totalMessages > 0 ? round(($sentMessages / $totalMessages) * 100, 1) : 0,
            'sent_messages' => $sentMessages,
            'failed_messages' => (clone $query)->where('status', 'failed')->count(),
        ];
    }

    private function getCostOverTime(): \Illuminate\Support\Collection
    {
        $query = SmsDeliveryLog::whereBetween('created_at', [$this->dateFrom, $this->dateTo . ' 23:59:59']);

        if ($this->notificationType !== 'all') {
            $query->where('notification_type', $this->notificationType);
        }

        $dateFormat = match ($this->groupBy) {
            'week' => "%Y-W%u",
            'month' => "%Y-%m",
            default => "%Y-%m-%d",
        };

        return $query->select(
            DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as date"),
            DB::raw('COALESCE(SUM(cost), 0) as total_cost'),
            DB::raw('COUNT(*) as total_messages'),
            DB::raw('COALESCE(SUM(segments), 0) as total_segments'),
        )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getCostByNotificationType(): \Illuminate\Support\Collection
    {
        return SmsDeliveryLog::whereBetween('created_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->select(
                'notification_type',
                DB::raw('COUNT(*) as total_messages'),
                DB::raw('COALESCE(SUM(cost), 0) as total_cost'),
                DB::raw('COALESCE(SUM(segments), 0) as total_segments'),
            )
            ->groupBy('notification_type')
            ->orderByDesc('total_cost')
            ->get();
    }

    private function getCostByStatus(): \Illuminate\Support\Collection
    {
        return SmsDeliveryLog::whereBetween('created_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->select(
                'status',
                DB::raw('COUNT(*) as total_messages'),
                DB::raw('COALESCE(SUM(cost), 0) as total_cost'),
                DB::raw('COALESCE(SUM(segments), 0) as total_segments'),
            )
            ->groupBy('status')
            ->orderByDesc('total_cost')
            ->get();
    }

    private function getTopCostDays(): \Illuminate\Support\Collection
    {
        return SmsDeliveryLog::whereBetween('created_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COALESCE(SUM(cost), 0) as total_cost'),
                DB::raw('COUNT(*) as total_messages'),
            )
            ->groupBy('date')
            ->orderByDesc('total_cost')
            ->limit(5)
            ->get();
    }
}
