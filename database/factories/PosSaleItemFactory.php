<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\{InventoryItem, PosSale};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PosSaleItem>
 */
class PosSaleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 10, 200);
        $quantity = fake()->numberBetween(1, 5);
        $subtotal = $unitPrice * $quantity;

        return [
            'pos_sale_id' => PosSale::factory(),
            'inventory_item_id' => InventoryItem::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'line_discount_amount' => 0,
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (\App\Models\PosSaleItem $posSaleItem) {
            // Recalculate subtotal after all attributes are set
            $posSaleItem->subtotal = $posSaleItem->quantity * $posSaleItem->unit_price;
        })->afterCreating(function (\App\Models\PosSaleItem $posSaleItem) {
            // Recalculate subtotal after all attributes are set
            $posSaleItem->subtotal = $posSaleItem->quantity * $posSaleItem->unit_price;
            $posSaleItem->save();
        });
    }
}
