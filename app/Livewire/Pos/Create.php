<?php

declare(strict_types=1);

namespace App\Livewire\Pos;

use App\Enums\{PosSaleStatus};
use App\Models\{Customer, InventoryItem, PosSale, PosSaleItem, ShopSettings};
use Illuminate\Support\Facades\{Auth, DB};
use Livewire\Attributes\{Computed, Layout};
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public array $cart = [];
    public string $customerId = '';
    public string $paymentMethod = 'cash';
    public string $discountAmount = '0';
    public string $notes = '';
    public string $searchTerm = '';

    public function mount()
    {
        $this->authorize('create', PosSale::class);
    }

    public function render()
    {
        $customers = Customer::orderBy('first_name')->orderBy('last_name')->get();
        $inventoryItems = InventoryItem::query()
            ->where('status', 'active')
            ->where('quantity', '>', 0)
            ->when($this->searchTerm, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->searchTerm . '%')
                        ->orWhere('sku', 'like', '%' . $this->searchTerm . '%');
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        return view('livewire.pos.create', [
            'customers' => $customers,
            'inventoryItems' => $inventoryItems,
        ]);
    }

    public function addToCart(string $itemId): void
    {
        $item = InventoryItem::findOrFail($itemId);

        if ($item->quantity <= 0) {
            $this->addError('cart', 'Item is out of stock.');
            return;
        }

        $cartKey = $itemId;

        if (isset($this->cart[$cartKey])) {
            $currentQty = $this->cart[$cartKey]['quantity'];
            if ($currentQty >= $item->quantity) {
                $this->addError('cart', 'Cannot add more than available stock.');
                return;
            }
            $this->cart[$cartKey]['quantity']++;
        } else {
            $this->cart[$cartKey] = [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'unit_price' => (string) $item->selling_price,
                'quantity' => 1,
                'max_quantity' => $item->quantity,
            ];
        }

        $this->searchTerm = '';
    }

    public function updateQuantity(string $cartKey, int $quantity): void
    {
        if ($quantity <= 0) {
            unset($this->cart[$cartKey]);
            return;
        }

        if (isset($this->cart[$cartKey])) {
            $maxQty = $this->cart[$cartKey]['max_quantity'];
            if ($quantity > $maxQty) {
                $this->addError('cart', 'Cannot exceed available stock.');
                return;
            }
            $this->cart[$cartKey]['quantity'] = $quantity;
        }
    }

    public function removeFromCart(string $cartKey): void
    {
        unset($this->cart[$cartKey]);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->customerId = '';
        $this->discountAmount = '0';
        $this->notes = '';
    }

    #[Computed]
    public function subtotal(): float
    {
        $total = 0;
        foreach ($this->cart as $item) {
            $total += (float) $item['unit_price'] * $item['quantity'];
        }
        return $total;
    }

    #[Computed]
    public function taxRate(): float
    {
        $settings = ShopSettings::getInstance();
        return (float) $settings->tax_rate;
    }

    #[Computed]
    public function taxAmount(): float
    {
        $discount = (float) $this->discountAmount;
        $taxableAmount = max(0, $this->subtotal() - $discount);
        return $taxableAmount * ($this->taxRate() / 100);
    }

    #[Computed]
    public function total(): float
    {
        $discount = (float) $this->discountAmount;
        return max(0, $this->subtotal() - $discount + $this->taxAmount());
    }

    public function checkout(): void
    {
        $this->authorize('create', PosSale::class);

        if (empty($this->cart)) {
            $this->addError('cart', 'Cart is empty. Add items before checkout.');
            return;
        }

        $validated = $this->validate([
            'customerId' => ['nullable', 'exists:customers,id'],
            'paymentMethod' => ['required', 'string', 'in:cash,card,bank_transfer'],
            'discountAmount' => ['nullable', 'numeric', 'min:0', 'max:' . $this->subtotal()],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated) {
            // Create the sale
            $sale = PosSale::create([
                'customer_id' => !empty($validated['customerId']) ? $validated['customerId'] : null,
                'subtotal' => $this->subtotal(),
                'tax_rate' => $this->taxRate(),
                'tax_amount' => $this->taxAmount(),
                'discount_amount' => (float) ($validated['discountAmount'] ?? 0),
                'total_amount' => $this->total(),
                'payment_method' => $validated['paymentMethod'],
                'notes' => $validated['notes'] ?? null,
                'sold_by' => Auth::id(),
                'sale_date' => now(),
                'status' => PosSaleStatus::Completed,
            ]);

            // Create sale items and update inventory
            foreach ($this->cart as $item) {
                PosSaleItem::create([
                    'pos_sale_id' => $sale->id,
                    'inventory_item_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => (float) $item['unit_price'] * $item['quantity'],
                    'line_discount_amount' => 0,
                ]);

                // Deduct from inventory
                $inventoryItem = InventoryItem::find($item['id']);
                $inventoryItem->decrement('quantity', $item['quantity']);
            }
        });

        $this->redirect(route('pos.index', ['success' => 'sale-completed']), navigate: true);
    }
}
