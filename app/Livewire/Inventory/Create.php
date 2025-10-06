<?php

declare(strict_types=1);

namespace App\Livewire\Inventory;

use App\Enums\InventoryStatus;
use App\Models\InventoryItem;
use Livewire\Attributes\{Layout, Validate};
use Livewire\{Component, WithFileUploads};

#[Layout('components.layouts.app')]
class Create extends Component
{
    use WithFileUploads;

    public function mount(): void
    {
        $this->authorize('create', InventoryItem::class);
    }

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|max:100|unique:inventory_items,sku')]
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
    public $image_path = null;

    public function save(): void
    {
        $this->authorize('create', InventoryItem::class);

        $this->validate();

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

        $item = InventoryItem::create($data);

        session()->flash('success', 'Inventory item created successfully.');

        $this->redirect(route('inventory.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.inventory.create', [
            'categories' => InventoryItem::distinct()->pluck('category')->filter()->sort()->values(),
        ]);
    }
}
