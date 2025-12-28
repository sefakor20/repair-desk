<?php

declare(strict_types=1);

namespace App\Livewire\Inventory;

use App\Models\InventoryItem;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LowStockAlert extends Component
{
    public string $alertType = 'all'; // all, low, critical, out

    #[Computed]
    public function lowStockItems()
    {
        return InventoryItem::lowStock()
            ->orderBy('quantity', 'asc')
            ->get();
    }

    #[Computed]
    public function outOfStockItems()
    {
        return InventoryItem::outOfStock()
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function criticalItems()
    {
        return InventoryItem::lowStock()
            ->whereRaw('quantity <= (reorder_level / 2)')
            ->orderBy('quantity', 'asc')
            ->get();
    }

    #[Computed]
    public function displayItems()
    {
        return match ($this->alertType) {
            'low' => $this->lowStockItems,
            'critical' => $this->criticalItems,
            'out' => $this->outOfStockItems,
            default => $this->lowStockItems->merge($this->outOfStockItems),
        };
    }

    #[Computed]
    public function totalAlerts(): int
    {
        return $this->lowStockItems->count() + $this->outOfStockItems->count();
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.inventory.low-stock-alert');
    }
}
