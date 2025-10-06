<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\{TicketPriority, TicketStatus};
use App\Models\{InventoryItem, Invoice, Payment, Ticket};
use Illuminate\Support\Facades\DB;
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
            'todayRevenue' => $this->getTodayRevenue(),
            'pendingInvoices' => $this->getPendingInvoices(),
            'lowStockItems' => $this->getLowStockItems(),
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

    protected function getTodayRevenue(): float
    {
        return Payment::whereDate('payment_date', today())
            ->sum('amount');
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

    protected function getLowStockItems(): int
    {
        return InventoryItem::whereColumn('quantity', '<=', 'reorder_level')
            ->count();
    }

    protected function getRecentTickets()
    {
        return Ticket::with(['customer', 'device', 'assignedTo'])
            ->latest()
            ->limit(5)
            ->get();
    }
}
