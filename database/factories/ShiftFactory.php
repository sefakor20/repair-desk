<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ShiftStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shift>
 */
class ShiftFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cashSales = fake()->randomFloat(2, 100, 1000);
        $cardSales = fake()->randomFloat(2, 200, 1500);
        $mobileMoneySales = fake()->randomFloat(2, 50, 500);
        $bankTransferSales = fake()->randomFloat(2, 100, 800);
        $totalSales = $cashSales + $cardSales + $mobileMoneySales + $bankTransferSales;
        $salesCount = fake()->numberBetween(5, 50);

        return [
            'shift_name' => fake()->randomElement(['Morning Shift', 'Afternoon Shift', 'Evening Shift', 'Night Shift']),
            'opened_by' => User::factory(),
            'closed_by' => User::factory(),
            'status' => ShiftStatus::Closed,
            'total_sales' => $totalSales,
            'sales_count' => $salesCount,
            'cash_sales' => $cashSales,
            'card_sales' => $cardSales,
            'mobile_money_sales' => $mobileMoneySales,
            'bank_transfer_sales' => $bankTransferSales,
            'opening_notes' => fake()->optional()->sentence(),
            'closing_notes' => fake()->optional()->sentence(),
            'started_at' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'ended_at' => fake()->dateTimeBetween('-1 day', 'now'),
        ];
    }

    public function open(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => ShiftStatus::Open,
            'closed_by' => null,
            'total_sales' => 0,
            'sales_count' => 0,
            'cash_sales' => 0,
            'card_sales' => 0,
            'mobile_money_sales' => 0,
            'bank_transfer_sales' => 0,
            'closing_notes' => null,
            'ended_at' => null,
            'started_at' => now(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => ShiftStatus::Closed,
        ]);
    }
}
