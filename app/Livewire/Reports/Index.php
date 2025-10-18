<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Enums\{InvoiceStatus, TicketStatus};
use App\Models\{Invoice, InventoryItem, Payment, Ticket, User, Branch};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\{Layout, Url};
use Livewire\Component;

#[Layout('components.layouts.app')]

class Index extends Component
{
    #[Url]
    public string $tab = 'sales';

    #[Url]
    public string $startDate = '';

    #[Url]
    public string $endDate = '';

    #[Url]
    public string $branchFilter = '';

    public function mount(): void
    {
        $this->authorize('viewReports', User::class);

        // Default to current month if not set
        if (empty($this->startDate)) {
            $this->startDate = now()->startOfMonth()->format('Y-m-d');
        }
        if (empty($this->endDate)) {
            $this->endDate = now()->endOfMonth()->format('Y-m-d');
        }
    }

    public function render()
    {
        $branches = Branch::active()->orderBy('name')->get();
        $data = match ($this->tab) {
            'sales' => $this->getSalesData(),
            'payments' => $this->getPaymentsData(),
            'technicians' => $this->getTechniciansData(),
            'inventory' => $this->getInventoryData(),
            'pos' => $this->getPosData(),
            default => $this->getSalesData(),
        };

        return view('livewire.reports.index', array_merge([
            'tab' => $this->tab,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'branches' => $branches,
            'branchFilter' => $this->branchFilter,
        ], $data));
    }

    public function updatedStartDate(): void
    {
        $this->validate([
            'startDate' => ['required', 'date'],
        ]);
    }

    public function updatedEndDate(): void
    {
        $this->validate([
            'startDate' => ['required', 'date'],
            'endDate' => ['required', 'date', 'after_or_equal:startDate'],
        ]);
    }

    protected function getSalesData(): array
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $invoiceQuery = Invoice::where('status', InvoiceStatus::Paid)
            ->whereBetween('updated_at', [$start, $end]);
        $pendingInvoiceQuery = Invoice::where('status', InvoiceStatus::Pending)
            ->whereBetween('updated_at', [$start, $end]);
        $paymentQuery = Payment::whereBetween('payment_date', [$start, $end]);

        if ($this->branchFilter) {
            $invoiceQuery->where('branch_id', $this->branchFilter);
            $pendingInvoiceQuery->where('branch_id', $this->branchFilter);
            $paymentQuery->where('branch_id', $this->branchFilter);
        }

        // Total revenue from paid invoices
        $totalRevenue = $invoiceQuery->sum('total');

        // Number of transactions
        $transactionCount = $invoiceQuery->count();

        // Revenue by payment method
        $revenueByMethod = $paymentQuery
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(fn($item) => [
                $item->payment_method->label() => (float) $item->total,
            ]);

        // Daily revenue trend
        $dailyRevenue = $paymentQuery
            ->select(DB::raw('DATE(payment_date) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(fn($item) => [
                Carbon::parse($item->date)->format('M d') => (float) $item->total,
            ]);

        // Average transaction value
        $avgTransaction = $transactionCount > 0 ? $totalRevenue / $transactionCount : 0;

        // Pending invoices
        $pendingInvoices = $pendingInvoiceQuery->sum('total');

        return [
            'totalRevenue' => $totalRevenue,
            'transactionCount' => $transactionCount,
            'avgTransaction' => $avgTransaction,
            'pendingInvoices' => $pendingInvoices,
            'revenueByMethod' => $revenueByMethod,
            'dailyRevenue' => $dailyRevenue,
        ];
    }

    protected function getPaymentsData(): array
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $paymentQuery = Payment::with(['invoice.customer', 'processedBy'])
            ->whereBetween('payment_date', [$start, $end]);
        if ($this->branchFilter) {
            $paymentQuery->where('branch_id', $this->branchFilter);
        }
        $payments = $paymentQuery->orderBy('payment_date', 'desc')->get();

        $totalCollected = $payments->sum('amount');

        $paymentsByMethod = $payments->groupBy(fn($payment) => $payment->payment_method->label())
            ->map(fn($group) => [
                'count' => $group->count(),
                'total' => $group->sum('amount'),
            ]);

        return [
            'payments' => $payments,
            'totalCollected' => $totalCollected,
            'paymentsByMethod' => $paymentsByMethod,
        ];
    }

    protected function getTechniciansData(): array
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $technicians = User::whereHas('assignedTickets', function ($query) use ($start, $end) {
            $query->whereBetween('created_at', [$start, $end]);
        })
            ->withCount([
                'assignedTickets as completed_tickets' => function ($query) use ($start, $end) {
                    $query->where('status', TicketStatus::Completed)
                        ->whereBetween('updated_at', [$start, $end]);
                },
                'assignedTickets as total_tickets' => function ($query) use ($start, $end) {
                    $query->whereBetween('created_at', [$start, $end]);
                },
                'assignedTickets as active_tickets' => function ($query) {
                    $query->whereIn('status', [TicketStatus::New, TicketStatus::InProgress]);
                },
            ])
            ->get()
            ->map(function ($technician) use ($start, $end) {
                // Calculate revenue from technician's completed tickets
                $revenue = Ticket::where('assigned_to', $technician->id)
                    ->where('status', TicketStatus::Completed)
                    ->whereBetween('updated_at', [$start, $end])
                    ->whereHas('invoice', fn($q) => $q->where('status', InvoiceStatus::Paid))
                    ->with('invoice')
                    ->get()
                    ->sum(fn($ticket) => $ticket->invoice->total ?? 0);

                // Calculate average resolution time
                $tickets = Ticket::where('assigned_to', $technician->id)
                    ->where('status', TicketStatus::Completed)
                    ->whereBetween('updated_at', [$start, $end])
                    ->get();

                $avgResolutionHours = $tickets->isNotEmpty()
                    ? $tickets->avg(fn($ticket) => $ticket->created_at->diffInHours($ticket->updated_at))
                    : 0;

                return [
                    'technician' => $technician,
                    'completed_tickets' => $technician->completed_tickets,
                    'total_tickets' => $technician->total_tickets,
                    'active_tickets' => $technician->active_tickets,
                    'revenue' => $revenue,
                    'avg_resolution_hours' => round($avgResolutionHours, 1),
                ];
            });

        return [
            'technicians' => $technicians,
        ];
    }

    protected function getInventoryData(): array
    {
        $lowStockItems = InventoryItem::whereColumn('quantity', '<=', 'reorder_level')
            ->orderBy('quantity')
            ->get();

        $totalInventoryValue = InventoryItem::sum(DB::raw('quantity * cost_price'));
        $totalRetailValue = InventoryItem::sum(DB::raw('quantity * selling_price'));

        // Most used parts (from ticket_parts)
        $mostUsedParts = DB::table('ticket_parts')
            ->join('inventory_items', 'ticket_parts.inventory_item_id', '=', 'inventory_items.id')
            ->select(
                'inventory_items.id',
                'inventory_items.name',
                'inventory_items.sku',
                DB::raw('SUM(ticket_parts.quantity) as total_quantity'),
                DB::raw('SUM(ticket_parts.quantity * ticket_parts.selling_price) as total_revenue'),
            )
            ->groupBy('inventory_items.id', 'inventory_items.name', 'inventory_items.sku')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        return [
            'lowStockItems' => $lowStockItems,
            'totalInventoryValue' => $totalInventoryValue,
            'totalRetailValue' => $totalRetailValue,
            'mostUsedParts' => $mostUsedParts,
        ];
    }

    protected function getPosData(): array
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $posSaleQuery = \App\Models\PosSale::with(['items.inventoryItem', 'customer', 'soldBy'])
            ->whereBetween('sale_date', [$start, $end])
            ->where('status', \App\Enums\PosSaleStatus::Completed);
        if ($this->branchFilter) {
            $posSaleQuery->where('branch_id', $this->branchFilter);
        }
        $posSales = $posSaleQuery->get();

        // Total POS revenue
        $totalRevenue = $posSales->sum('total_amount');

        // Transaction count
        $transactionCount = $posSales->count();

        // Average transaction value
        $avgTransaction = $transactionCount > 0 ? $totalRevenue / $transactionCount : 0;

        // Total items sold
        $totalItemsSold = $posSales->sum('total_items');

        // Revenue by payment method
        $revenueByMethod = $posSales->groupBy(fn($sale) => $sale->payment_method->label())
            ->map(fn($group) => [
                'count' => $group->count(),
                'total' => $group->sum('total_amount'),
                'percentage' => $transactionCount > 0 ? round(($group->count() / $transactionCount) * 100, 1) : 0,
            ])
            ->sortByDesc('total');

        // Daily sales trend
        $dailySales = $posSales->groupBy(fn($sale) => $sale->sale_date->format('Y-m-d'))
            ->map(fn($group) => [
                'date' => $group->first()->sale_date->format('M d'),
                'count' => $group->count(),
                'total' => $group->sum('total_amount'),
            ])
            ->sortKeys()
            ->values();

        // Top products by quantity
        $topProductsByQuantity = DB::table('pos_sale_items')
            ->join('pos_sales', 'pos_sale_items.pos_sale_id', '=', 'pos_sales.id')
            ->join('inventory_items', 'pos_sale_items.inventory_item_id', '=', 'inventory_items.id')
            ->where('pos_sales.status', \App\Enums\PosSaleStatus::Completed->value)
            ->whereBetween('pos_sales.sale_date', [$start, $end]);
    }

}
