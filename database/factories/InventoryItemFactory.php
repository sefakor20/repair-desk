<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InventoryStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['Screen', 'Battery', 'Charger', 'Case', 'Cable', 'Adapter', 'Tool', 'Other'];
        $parts = [
            'Screen' => ['iPhone 12 Screen', 'Samsung Galaxy S21 Screen', 'iPad Pro Display'],
            'Battery' => ['iPhone Battery 3000mAh', 'Android Battery Pack', 'MacBook Battery'],
            'Charger' => ['USB-C Fast Charger', 'Lightning Cable Charger', 'Wireless Charging Pad'],
            'Case' => ['Protective Phone Case', 'Tablet Case', 'Laptop Sleeve'],
            'Cable' => ['USB-C to USB-C Cable', 'Lightning Cable', 'HDMI Cable'],
        ];

        $category = fake()->randomElement($categories);
        $name = isset($parts[$category]) ? fake()->randomElement($parts[$category]) : fake()->words(3, true);
        $cost = fake()->randomFloat(2, 5, 200);
        $markup = fake()->randomFloat(2, 1.2, 2.5);

        return [
            'name' => $name,
            'sku' => fake()->unique()->bothify('SKU-####-???'),
            'description' => fake()->optional()->sentence(),
            'category' => $category,
            'cost_price' => $cost,
            'selling_price' => round($cost * $markup, 2),
            'quantity' => fake()->numberBetween(0, 100),
            'reorder_level' => fake()->numberBetween(5, 20),
            'status' => fake()->randomElement(InventoryStatus::cases()),
        ];
    }

    /**
     * Indicate that the item is low on stock.
     */
    public function lowStock(): static
    {
        return $this->state(fn(array $attributes) => [
            'quantity' => fake()->numberBetween(0, $attributes['reorder_level'] - 1),
        ]);
    }

    /**
     * Indicate that the item is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn(array $attributes) => [
            'quantity' => 0,
        ]);
    }
}
