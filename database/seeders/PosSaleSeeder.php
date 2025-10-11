<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PosSaleStatus;
use App\Models\{Customer, InventoryItem, PosSale, PosSaleItem, User};
use Illuminate\Database\Seeder;

class PosSaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $customers = Customer::all();
        $inventoryItems = InventoryItem::where('quantity', '>', 0)->get();

        if ($users->isEmpty() || $inventoryItems->isEmpty()) {
            return;
        }

        // Create 15 completed sales with items
        for ($i = 0; $i < 15; $i++) {
            $itemCount = rand(1, 4);
            $selectedItems = $inventoryItems->random(min($itemCount, $inventoryItems->count()));

            $subtotal = 0;
            $saleItems = [];

            foreach ($selectedItems as $item) {
                $quantity = rand(1, 3);
                $unitPrice = (float) $item->selling_price;
                $itemSubtotal = $unitPrice * $quantity;
                $subtotal += $itemSubtotal;

                $saleItems[] = [
                    'inventory_item_id' => $item->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $taxRate = 8.5;
            $discountAmount = rand(0, 1) ? 0 : rand(5, 20);
            $taxAmount = ($subtotal - $discountAmount) * ($taxRate / 100);
            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            $sale = PosSale::create([
                'customer_id' => rand(0, 1) ? $customers->random()->id : null,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'payment_method' => ['cash', 'card', 'bank_transfer'][rand(0, 2)],
                'notes' => rand(0, 1) ? null : 'Quick sale',
                'sold_by' => $users->random()->id,
                'sale_date' => now()->subDays(rand(0, 60)),
                'status' => PosSaleStatus::Completed,
            ]);

            foreach ($saleItems as $saleItem) {
                PosSaleItem::create([
                    'pos_sale_id' => $sale->id,
                    ...$saleItem,
                    'line_discount_amount' => 0,
                ]);
            }
        }

        // Create 2 refunded sales
        for ($i = 0; $i < 2; $i++) {
            $item = $inventoryItems->random();
            $quantity = rand(1, 2);
            $subtotal = (float) $item->selling_price * $quantity;
            $taxRate = 8.5;
            $taxAmount = $subtotal * ($taxRate / 100);
            $totalAmount = $subtotal + $taxAmount;

            $sale = PosSale::create([
                'customer_id' => $customers->random()->id,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'discount_amount' => 0,
                'total_amount' => $totalAmount,
                'payment_method' => 'card',
                'notes' => 'REFUND: Customer not satisfied (Refunded on ' . now()->subDays(rand(1, 30))->format('Y-m-d H:i') . ')',
                'sold_by' => $users->random()->id,
                'sale_date' => now()->subDays(rand(5, 60)),
                'status' => PosSaleStatus::Refunded,
            ]);

            PosSaleItem::create([
                'pos_sale_id' => $sale->id,
                'inventory_item_id' => $item->id,
                'quantity' => $quantity,
                'unit_price' => $item->selling_price,
                'subtotal' => $subtotal,
                'line_discount_amount' => 0,
            ]);
        }
    }
}
