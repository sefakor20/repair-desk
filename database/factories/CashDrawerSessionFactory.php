<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CashDrawerStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashDrawerSessionFactory extends Factory
{
    public function definition(): array
    {
        $openingBalance = fake()->randomFloat(2, 100, 1000);
        $cashSales = fake()->randomFloat(2, 50, 500);
        $cashIn = fake()->randomFloat(2, 0, 100);
        $cashOut = fake()->randomFloat(2, 0, 100);
        $expectedBalance = $openingBalance + $cashSales + $cashIn - $cashOut;
        $actualBalance = $expectedBalance + fake()->randomFloat(2, -10, 10);

        return [
            'opened_by' => User::factory(),
            'closed_by' => User::factory(),
            'opening_balance' => $openingBalance,
            'expected_balance' => $expectedBalance,
            'actual_balance' => $actualBalance,
            'cash_sales' => $cashSales,
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'discrepancy' => $actualBalance - $expectedBalance,
            'status' => CashDrawerStatus::Closed,
            'opening_notes' => fake()->optional()->sentence(),
            'closing_notes' => fake()->optional()->sentence(),
            'opened_at' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'closed_at' => fake()->dateTimeBetween('-1 day', 'now'),
        ];
    }

    public function open(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => CashDrawerStatus::Open,
            'closed_by' => null,
            'expected_balance' => null,
            'actual_balance' => null,
            'cash_sales' => 0,
            'cash_in' => 0,
            'cash_out' => 0,
            'discrepancy' => null,
            'closing_notes' => null,
            'closed_at' => null,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => CashDrawerStatus::Closed,
        ]);
    }
}
