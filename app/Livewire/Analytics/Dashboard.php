<?php

declare(strict_types=1);

namespace App\Livewire\Analytics;

use App\Enums\PaymentMethod;
use App\Enums\PosSaleStatus;
use App\Models\PosSale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    public string $period = 'all';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public function mount(): void
    {
        $this->setPeriodDates();
    }

    public function updatedPeriod(): void
    {
        $this->setPeriodDates();
    }

    private function setPeriodDates(): void
    {
        match ($this->period) {
            'today' => [
                $this->startDate = now()->startOfDay()->toDateTimeString(),
                $this->endDate = now()->endOfDay()->toDateTimeString(),
            ],
            'week' => [
                $this->startDate = now()->startOfWeek()->toDateTimeString(),
                $this->endDate = now()->endOfWeek()->toDateTimeString(),
            ],
            'month' => [
                $this->startDate = now()->startOfMonth()->toDateTimeString(),
                $this->endDate = now()->endOfMonth()->toDateTimeString(),
            ],
            'year' => [
                $this->startDate = now()->startOfYear()->toDateTimeString(),
                $this->endDate = now()->endOfYear()->toDateTimeString(),
            ],
            default => [
                $this->startDate = null,
                $this->endDate = null,
            ],
        };
    }

    #[Computed]
    public function salesAggregates(): object
    {
        $query = $this->salesQuery()
            ->selectRaw('
                COUNT(*) as total_sales,
                COALESCE(SUM(total_amount), 0) as total_revenue,
                COALESCE(SUM(tax_amount), 0) as total_tax,
                COALESCE(SUM(discount_amount), 0) as total_discount
            ')
            ->first();

        return (object) [
            'total_sales' => (int) $query->total_sales,
            'total_revenue' => (float) $query->total_revenue,
            'total_tax' => (float) $query->total_tax,
            'total_discount' => (float) $query->total_discount,
        ];
    }

    #[Computed]
    public function totalRevenue(): float
    {
        return $this->salesAggregates()->total_revenue;
    }

    #[Computed]
    public function totalSales(): int
    {
        return $this->salesAggregates()->total_sales;
    }

    #[Computed]
    public function averageOrderValue(): float
    {
        $aggregates = $this->salesAggregates();

        return $aggregates->total_sales > 0
            ? $aggregates->total_revenue / $aggregates->total_sales
            : 0.0;
    }

    #[Computed]
    public function totalTax(): float
    {
        return $this->salesAggregates()->total_tax;
    }

    #[Computed]
    public function totalDiscount(): float
    {
        return $this->salesAggregates()->total_discount;
    }

    #[Computed]
    public function topProducts(): array
    {
        $query = DB::table('pos_sale_items')
            ->join('inventory_items', 'pos_sale_items.inventory_item_id', '=', 'inventory_items.id')
            ->join('pos_sales', 'pos_sale_items.pos_sale_id', '=', 'pos_sales.id')
            ->where('pos_sales.status', PosSaleStatus::Completed->value)
            ->select(
                'inventory_items.name',
                'inventory_items.sku',
                DB::raw('SUM(pos_sale_items.quantity) as total_quantity'),
                DB::raw('SUM(pos_sale_items.subtotal) as total_revenue'),
            )
            ->groupBy('inventory_items.id', 'inventory_items.name', 'inventory_items.sku')
            ->orderByDesc('total_revenue')
            ->limit(10);

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('pos_sales.sale_date', [$this->startDate, $this->endDate]);
        }

        return $query->get()->map(function ($product) {
            return (object) [
                'name' => $product->name,
                'sku' => $product->sku,
                'total_quantity' => (int) $product->total_quantity,
                'total_revenue' => (float) $product->total_revenue,
            ];
        })->toArray();
    }

    #[Computed]
    public function paymentMethodBreakdown(): array
    {
        $query = $this->salesQuery()
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total'),
            )
            ->groupBy('payment_method');

        $results = $query->get();
        $totalRevenue = $this->totalRevenue();

        return $results->map(function ($result) use ($totalRevenue) {
            $paymentMethod = is_string($result->payment_method)
                ? PaymentMethod::from($result->payment_method)
                : $result->payment_method;

            $total = (float) $result->total;

            return [
                'method' => $paymentMethod->label(),
                'count' => $result->count,
                'total' => $total,
                'percentage' => $totalRevenue > 0
                    ? round(($total / $totalRevenue) * 100, 1)
                    : 0,
            ];
        })->toArray();
    }

    #[Computed]
    public function dailySales(): array
    {
        $startDate = $this->startDate ? Carbon::parse($this->startDate) : now()->subDays(30);
        $endDate = $this->endDate ? Carbon::parse($this->endDate) : now();

        $sales = PosSale::where('status', PosSaleStatus::Completed)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total'),
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $sales->map(function ($sale) {
            return [
                'date' => Carbon::parse($sale->date)->format('M d'),
                'count' => $sale->count,
                'total' => (float) $sale->total,
            ];
        })->toArray();
    }

    #[Computed]
    public function revenueGrowth(): array
    {
        $currentRevenue = $this->totalRevenue();

        // Get previous period revenue
        $previousRevenue = match ($this->period) {
            'today' => (float) PosSale::where('status', PosSaleStatus::Completed)
                ->whereDate('sale_date', now()->subDay())
                ->sum('total_amount'),
            'week' => (float) PosSale::where('status', PosSaleStatus::Completed)
                ->whereBetween('sale_date', [
                    now()->subWeek()->startOfWeek(),
                    now()->subWeek()->endOfWeek(),
                ])
                ->sum('total_amount'),
            'month' => (float) PosSale::where('status', PosSaleStatus::Completed)
                ->whereBetween('sale_date', [
                    now()->subMonth()->startOfMonth(),
                    now()->subMonth()->endOfMonth(),
                ])
                ->sum('total_amount'),
            'year' => (float) PosSale::where('status', PosSaleStatus::Completed)
                ->whereBetween('sale_date', [
                    now()->subYear()->startOfYear(),
                    now()->subYear()->endOfYear(),
                ])
                ->sum('total_amount'),
            default => 0,
        };

        $growth = $previousRevenue > 0
            ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100
            : 0;

        return [
            'current' => $currentRevenue,
            'previous' => $previousRevenue,
            'percentage' => round($growth, 1),
            'direction' => $growth >= 0 ? 'up' : 'down',
        ];
    }

    private function salesQuery()
    {
        $query = PosSale::where('status', PosSaleStatus::Completed);

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('sale_date', [$this->startDate, $this->endDate]);
        }

        return $query;
    }

    public function render()
    {
        return view('livewire.analytics.dashboard')
            ->layout('components.layouts.app');
    }
}
