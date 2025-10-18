<?php

declare(strict_types=1);

namespace App\Livewire\Inventory;

use App\Enums\InventoryStatus;
use App\Models\InventoryItem;
use App\Models\Branch;
use Livewire\Attributes\{Layout, Url};
use Livewire\{Component, WithPagination};

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $category = '';

    #[Url]
    public bool $lowStock = false;

    #[Url]
    public string $branchFilter = '';

    public ?string $deletingItemId = null;

    public function render()
    {
        $branches = Branch::active()->orderBy('name')->get();
        $items = InventoryItem::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('sku', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', InventoryStatus::from($this->status));
            })
            ->when($this->category, fn($query) => $query->where('category', $this->category))
            ->when($this->lowStock, fn($query) => $query->whereColumn('quantity', '<=', 'reorder_level'))
            ->when($this->branchFilter, fn($query) => $query->where('branch_id', $this->branchFilter))
            ->latest()
            ->paginate(15);

        $categories = InventoryItem::distinct()->pluck('category')->filter()->sort()->values();

        return view('livewire.inventory.index', [
            'items' => $items,
            'categories' => $categories,
            'branches' => $branches,
        ]);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatingLowStock(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'category', 'lowStock', 'branchFilter']);
        $this->resetPage();
    }

    public function confirmDelete($itemId): void
    {
        $this->deletingItemId = $itemId;
    }

    public function delete(): void
    {
        if (!$this->deletingItemId) {
            return;
        }

        $item = InventoryItem::findOrFail($this->deletingItemId);

        $this->authorize('delete', $item);

        $item->delete();

        $this->deletingItemId = null;

        session()->flash('success', 'Inventory item deleted successfully.');
    }

    public function cancelDelete(): void
    {
        $this->deletingItemId = null;
    }
}
