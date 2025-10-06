<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\{Customer, Ticket};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $laborCost = fake()->randomFloat(2, 20, 200);
        $partsCost = fake()->randomFloat(2, 0, 500);
        $subtotal = $laborCost + $partsCost;
        $tax = round($subtotal * 0.08, 2);
        $total = $subtotal + $tax;

        return [
            'ticket_id' => Ticket::factory(),
            'customer_id' => Customer::factory(),
            'labor_cost' => $laborCost,
            'parts_cost' => $partsCost,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'status' => fake()->randomElement(InvoiceStatus::cases()),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the invoice is paid.
     */
    public function paid(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => InvoiceStatus::Paid,
        ]);
    }

    /**
     * Indicate that the invoice is pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => InvoiceStatus::Pending,
        ]);
    }
}
