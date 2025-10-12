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
    public function render()
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
        $tickets = Ticket::all()->groupBy(fn($ticket) => $ticket->status->value);

        return [
            'new' => $tickets->get('new')?->count() ?? 0,
            'in_progress' => $tickets->get('in_progress')?->count() ?? 0,
            'waiting_for_parts' => $tickets->get('waiting_for_parts')?->count() ?? 0,
            'completed' => $tickets->get('completed')?->count() ?? 0,
            'delivered' => $tickets->get('delivered')?->count() ?? 0,
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
        // Count urgent tickets created today
        $today = Ticket::where('priority', TicketPriority::Urgent)
            ->whereIn('status', [TicketStatus::New, TicketStatus::InProgress])
            ->whereDate('created_at', today())
            ->count();

        // Count urgent tickets created yesterday
        $yesterday = Ticket::where('priority', TicketPriority::Urgent)
            ->whereIn('status', [TicketStatus::New, TicketStatus::InProgress])
            ->whereDate('created_at', today()->subDay())
            ->count();

        return $this->calculateTrend($today, $yesterday);
    }

    protected function getTodayRevenue(): float
    {
        return (float) Payment::whereDate('payment_date', today())
            ->sum('amount');
    }

    protected function getTodayRevenueTrend(): array
    {
        $today = $this->getTodayRevenue();
        $yesterday = (float) Payment::whereDate('payment_date', today()->subDay())
            ->sum('amount');

        return $this->calculateTrend($today, $yesterday);
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
        $today = Invoice::where('status', 'pending')
            ->whereDate('created_at', today())
            ->count();

        $yesterday = Invoice::where('status', 'pending')
            ->whereDate('created_at', today()->subDay())
            ->count();

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
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $count = Ticket::where('priority', TicketPriority::Urgent)
                ->whereIn('status', [TicketStatus::New, TicketStatus::InProgress])
                ->whereDate('created_at', $date)
                ->count();
            $data[] = $count;
        }

        return $data;
    }

    protected function getRevenueSparkline(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $amount = (float) Payment::whereDate('payment_date', $date)
                ->sum('amount');
            $data[] = $amount;
        }

        return $data;
    }

    protected function getPendingInvoicesSparkline(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $count = Invoice::where('status', 'pending')
                ->whereDate('created_at', $date)
                ->count();
            $data[] = $count;
        }

        return $data;
    }

    protected function getLowStockSparkline(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $count = InventoryItem::whereColumn('quantity', '<=', 'reorder_level')
                ->whereDate('updated_at', '<=', $date)
                ->count();
            $data[] = $count;
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
