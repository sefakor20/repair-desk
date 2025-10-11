<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\{PaymentMethod, PosSaleStatus};
use App\Models\{Customer, User};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PosSale>
 */
class PosSaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 20, 500);
        $taxRate = 8.5;
        $discountAmount = fake()->boolean(30) ? fake()->randomFloat(2, 0, $subtotal * 0.2) : 0;
        $taxAmount = ($subtotal - $discountAmount) * ($taxRate / 100);
        $totalAmount = $subtotal - $discountAmount + $taxAmount;

        return [
            'customer_id' => fake()->boolean(70) ? Customer::factory() : null,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
            'notes' => fake()->optional()->sentence(),
            'sold_by' => User::factory(),
            'sale_date' => fake()->dateTimeBetween('-2 months', 'now'),
            'status' => fake()->randomElement(PosSaleStatus::cases()),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => PosSaleStatus::Completed,
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => PosSaleStatus::Refunded,
            'notes' => 'REFUND: Customer requested refund (Refunded on ' . now()->format('Y-m-d H:i') . ')',
        ]);
    }
}
