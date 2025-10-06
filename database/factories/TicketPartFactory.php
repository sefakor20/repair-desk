<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\{InventoryItem, Ticket};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketPart>
 */
class TicketPartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $costPrice = fake()->randomFloat(2, 5, 100);
        $sellingPrice = $costPrice * fake()->randomFloat(2, 1.2, 2.5);

        return [
            'ticket_id' => Ticket::factory(),
            'inventory_item_id' => fake()->optional()->randomElement(InventoryItem::pluck('id')->toArray()),
            'part_name' => fake()->randomElement([
                'LCD Screen',
                'Battery',
                'Charging Port',
                'Home Button',
                'Camera Module',
                'Speaker',
                'Microphone',
                'Power Button',
                'Volume Button',
                'Back Cover',
            ]),
            'quantity' => fake()->numberBetween(1, 3),
            'cost_price' => $costPrice,
            'selling_price' => round($sellingPrice, 2),
        ];
    }
}
