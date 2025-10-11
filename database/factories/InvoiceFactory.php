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
        $subtotal = fake()->randomFloat(2, 100, 1000);
        $discount = fake()->optional(0.3)->randomFloat(2, 0, $subtotal * 0.2) ?? 0;
        $taxRate = fake()->randomFloat(2, 0, 15);
        $taxAmount = round(($subtotal - $discount) * ($taxRate / 100), 2);
        $total = $subtotal - $discount + $taxAmount;

        return [
            'ticket_id' => Ticket::factory(),
            'customer_id' => Customer::factory(),
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'discount' => $discount,
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
