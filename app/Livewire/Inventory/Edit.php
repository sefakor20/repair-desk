<?php

declare(strict_types=1);

namespace App\Livewire\Inventory;

use App\Enums\InventoryStatus;
use App\Models\InventoryItem;
use Livewire\Attributes\{Layout, Validate};
use Livewire\{Component, WithFileUploads};

#[Layout('components.layouts.app')]
class Edit extends Component
{
    use WithFileUploads;

    public InventoryItem $item;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|max:100')]
    public string $sku = '';

    #[Validate('nullable|string|max:1000')]
    public ?string $description = null;

    #[Validate('nullable|string|max:100')]
    public ?string $category = null;

    #[Validate('required|numeric|min:0')]
    public string $cost_price = '';

    #[Validate('required|numeric|min:0')]
    public string $selling_price = '';

    #[Validate('required|integer|min:0')]
    public string $quantity = '';

    #[Validate('required|integer|min:0')]
    public string $reorder_level = '';

    #[Validate('required|in:active,inactive')]
    public string $status = 'active';

    #[Validate('nullable|image|max:2048')]
    public $image_path;

    public function mount(InventoryItem $item): void
    {
        $this->authorize('update', $item);

        $this->item = $item;
        $this->name = $item->name;
        $this->sku = $item->sku;
        $this->description = $item->description;
        $this->category = $item->category;
        $this->cost_price = (string) $item->cost_price;
        $this->selling_price = (string) $item->selling_price;
        $this->quantity = (string) $item->quantity;
        $this->reorder_level = (string) $item->reorder_level;
        $this->status = $item->status->value;
    }

    public function update(): void
    {
        $this->authorize('update', $this->item);

        $this->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:inventory_items,sku,' . $this->item->id,
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
            'image_path' => 'nullable|image|max:2048',
        ]);

        $data = [
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'category' => $this->category,
            'cost_price' => $this->cost_price,
            'selling_price' => $this->selling_price,
            'quantity' => $this->quantity,
            'reorder_level' => $this->reorder_level,
            'status' => InventoryStatus::from($this->status),
        ];

        if ($this->image_path) {
            $data['image_path'] = $this->image_path->store('inventory', 'public');
        }

        $this->item->update($data);

        session()->flash('success', 'Inventory item updated successfully.');

        $this->redirect(route('inventory.index'), navigate: true);
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.inventory.edit', [
            'categories' => InventoryItem::distinct()->pluck('category')->filter()->sort()->values(),
        ]);
    }
}
