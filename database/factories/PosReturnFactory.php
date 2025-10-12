<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\{ReturnReason, ReturnStatus};
use App\Models\{PosSale, User};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PosReturn>
 */
class PosReturnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotalReturned = fake()->randomFloat(2, 20, 500);
        $taxReturned = $subtotalReturned * 0.05;
        $restockingFee = fake()->boolean(30) ? ($subtotalReturned + $taxReturned) * 0.10 : 0;
        $totalRefund = ($subtotalReturned + $taxReturned) - $restockingFee;

        return [
            'return_number' => 'RET-' . now()->format('Ymd') . '-' . fake()->unique()->numberBetween(1000, 9999),
            'original_sale_id' => PosSale::factory(),
            'customer_id' => fn(array $attributes) => PosSale::find($attributes['original_sale_id'])?->customer_id,
            'processed_by' => User::factory(),
            'shift_id' => null,
            'return_reason' => fake()->randomElement(ReturnReason::cases()),
            'return_notes' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(ReturnStatus::cases()),
            'subtotal_returned' => $subtotalReturned,
            'tax_returned' => $taxReturned,
            'restocking_fee' => $restockingFee,
            'total_refund_amount' => $totalRefund,
            'refund_method' => fake()->randomElement(['cash', 'card', 'original_method']),
            'refund_reference' => fake()->optional()->uuid(),
            'refund_metadata' => null,
            'refunded_at' => fake()->boolean(60) ? fake()->dateTimeBetween('-1 month', 'now') : null,
            'inventory_restored' => fake()->boolean(70),
            'return_date' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => ReturnStatus::Pending,
            'refunded_at' => null,
            'inventory_restored' => false,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => ReturnStatus::Approved,
            'refunded_at' => now(),
            'inventory_restored' => true,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => ReturnStatus::Rejected,
            'refunded_at' => null,
            'inventory_restored' => false,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => ReturnStatus::Completed,
            'refunded_at' => now(),
            'inventory_restored' => true,
        ]);
    }
}
