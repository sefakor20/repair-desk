<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\{TicketPriority, TicketStatus};
use App\Models\{InventoryItem, Invoice, Payment, Ticket};
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.dashboard', [
            'ticketsByStatus' => $this->getTicketsByStatus(),
            'urgentTickets' => $this->getUrgentTickets(),
            'urgentTicketsTrend' => $this->getUrgentTicketsTrend(),
            'urgentTicketsSparkline' => $this->getUrgentTicketsSparkline(),
            'todayRevenue' => $this->getTodayRevenue(),
            'todayRevenueTrend' => $this->getTodayRevenueTrend(),
            'revenueSparkline' => $this->getRevenueSparkline(),
            'pendingInvoices' => $this->getPendingInvoices(),
            'pendingInvoicesTrend' => $this->getPendingInvoicesTrend(),
            'pendingInvoicesSparkline' => $this->getPendingInvoicesSparkline(),
            'lowStockItems' => $this->getLowStockItems(),
            'lowStockItemsTrend' => $this->getLowStockItemsTrend(),
            'lowStockSparkline' => $this->getLowStockSparkline(),
            'recentTickets' => $this->getRecentTickets(),
        ]);
    }

    protected function getTicketsByStatus(): array
    {
        $tickets = Ticket::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'new' => $tickets->get('new') ?? 0,
            'in_progress' => $tickets->get('in_progress') ?? 0,
            'waiting_for_parts' => $tickets->get('waiting_for_parts') ?? 0,
            'completed' => $tickets->get('completed') ?? 0,
            'delivered' => $tickets->get('delivered') ?? 0,
        ];
    }

    protected function getUrgentTickets(): int
    {
        return Ticket::where('priority', TicketPriority::Urgent)
            ->whereIn('status', [TicketStatus::New, TicketStatus::InProgress])
            ->count();
    }

    protected function getUrgentTicketsTrend(): array
    {
        $results = Ticket::where('priority', TicketPriority::Urgent)
            ->whereIn('status', [TicketStatus::New, TicketStatus::InProgress])
            ->whereBetween('created_at', [today()->subDay()->startOfDay(), today()->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $today = $results->get(today()->toDateString(), 0);
        $yesterday = $results->get(today()->subDay()->toDateString(), 0);

        return $this->calculateTrend($today, $yesterday);
    }

    protected function getTodayRevenue(): float
    {
        return (float) Payment::whereDate('payment_date', today())
            ->sum('amount');
    }

    protected function getTodayRevenueTrend(): array
    {
        $yesterday = today()->subDay();
        $results = Payment::whereBetween('payment_date', [$yesterday->startOfDay(), today()->endOfDay()])
            ->selectRaw('DATE(payment_date) as date, COALESCE(SUM(amount), 0) as total')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $today = (float) ($results->get(today()->toDateString())->total ?? 0);
        $yesterdayAmount = (float) ($results->get(today()->subDay()->toDateString())->total ?? 0);

        return $this->calculateTrend($today, $yesterdayAmount);
    }

    protected function getPendingInvoices(): array
    {
        $pendingInvoices = Invoice::where('status', 'pending')
            ->with('payments')
            ->get();

        $total = $pendingInvoices->sum(fn($invoice) => $invoice->balance_due);

        return [
            'count' => $pendingInvoices->count(),
            'total' => $total,
        ];
    }

    protected function getPendingInvoicesTrend(): array
    {
        $results = Invoice::where('status', 'pending')
            ->whereBetween('created_at', [today()->subDay()->startOfDay(), today()->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $today = $results->get(today()->toDateString(), 0);
        $yesterday = $results->get(today()->subDay()->toDateString(), 0);

        return $this->calculateTrend($today, $yesterday);
    }

    protected function getLowStockItems(): int
    {
        return InventoryItem::whereColumn('quantity', '<=', 'reorder_level')
            ->count();
    }

    protected function getLowStockItemsTrend(): array
    {
        // For low stock, we want to track the change in low stock items
        $today = $this->getLowStockItems();
        $yesterday = InventoryItem::whereColumn('quantity', '<=', 'reorder_level')
            ->whereDate('updated_at', '<=', today()->subDay())
            ->count();

        return $this->calculateTrend($today, $yesterday, true); // true = lower is better
    }

    protected function calculateTrend(float|int $current, float|int $previous, bool $invertDirection = false): array
    {
        if ($previous == 0) {
            return [
                'percentage' => $current > 0 ? 100 : 0,
                'direction' => $current > 0 ? 'up' : 'neutral',
                'isPositive' => $invertDirection ? $current <= 0 : $current >= 0,
            ];
        }

        $change = $current - $previous;
        $percentage = abs(round(($change / $previous) * 100, 1));
        $direction = $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral');

        // For metrics where lower is better (like low stock items), invert the positive/negative
        $isPositive = $invertDirection
            ? $change <= 0  // Lower is better
            : $change >= 0; // Higher is better

        return [
            'percentage' => $percentage,
            'direction' => $direction,
            'isPositive' => $isPositive,
        ];
    }

    protected function getUrgentTicketsSparkline(): array
    {
        $startDate = today()->subDays(6);
        $endDate = today();

        $results = Ticket::where('priority', TicketPriority::Urgent)
            ->whereIn('status', [TicketStatus::New, TicketStatus::InProgress])
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i)->toDateString();
            $data[] = $results->get($date, 0);
        }

        return $data;
    }

    protected function getRevenueSparkline(): array
    {
        $startDate = today()->subDays(6);
        $endDate = today();

        $results = Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i)->toDateString();
            $data[] = (float) ($results->get($date, 0));
        }

        return $data;
    }

    protected function getPendingInvoicesSparkline(): array
    {
        $startDate = today()->subDays(6);
        $endDate = today();

        $results = Invoice::where('status', 'pending')
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i)->toDateString();
            $data[] = $results->get($date, 0);
        }

        return $data;
    }

    protected function getLowStockSparkline(): array
    {
        // Low stock sparkline is tricky because it's based on current state,
        // not historical data. We'll keep a simplified version.
        // For accurate historical tracking, you'd need a daily snapshot table.
        $data = [];
        $currentCount = $this->getLowStockItems();

        // Fill with current count as approximation
        for ($i = 0; $i < 7; $i++) {
            $data[] = $currentCount;
        }

        return $data;
    }

    protected function getRecentTickets()
    {
        return Ticket::with(['customer', 'device', 'assignedTo'])
            ->latest()
            ->limit(5)
            ->get();
    }
}
