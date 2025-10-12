<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\{InventoryItem, PosReturn, PosSaleItem};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PosReturnItem>
 */
class PosReturnItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantityReturned = fake()->numberBetween(1, 5);
        $unitPrice = fake()->randomFloat(2, 10, 200);
        $subtotal = $quantityReturned * $unitPrice;
        $lineRefundAmount = $subtotal;

        return [
            'pos_return_id' => PosReturn::factory(),
            'original_sale_item_id' => PosSaleItem::factory(),
            'inventory_item_id' => fn(array $attributes) => PosSaleItem::find($attributes['original_sale_item_id'])?->inventory_item_id ?? InventoryItem::factory(),
            'quantity_returned' => $quantityReturned,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'line_refund_amount' => $lineRefundAmount,
            'item_condition' => fake()->randomElement(['good', 'damaged', 'defective']),
            'item_notes' => fake()->optional()->sentence(),
        ];
    }

    public function good(): static
    {
        return $this->state(fn(array $attributes) => [
            'item_condition' => 'good',
            'item_notes' => 'Item in perfect condition',
        ]);
    }

    public function damaged(): static
    {
        return $this->state(fn(array $attributes) => [
            'item_condition' => 'damaged',
            'item_notes' => fake()->randomElement([
                'Minor scratches on surface',
                'Water damage detected',
                'Screen cracked',
                'Dented corner',
            ]),
        ]);
    }

    public function defective(): static
    {
        return $this->state(fn(array $attributes) => [
            'item_condition' => 'defective',
            'item_notes' => fake()->randomElement([
                'Does not power on',
                'Not functioning properly',
                'Missing parts',
                'Wrong specification',
            ]),
        ]);
    }
}
