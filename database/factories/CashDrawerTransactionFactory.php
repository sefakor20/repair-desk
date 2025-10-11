<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CashTransactionType;
use App\Models\CashDrawerSession;
use App\Models\PosSale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashDrawerTransactionFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(CashTransactionType::cases());

        return [
            'cash_drawer_session_id' => CashDrawerSession::factory(),
            'user_id' => User::factory(),
            'pos_sale_id' => $type === CashTransactionType::Sale ? PosSale::factory() : null,
            'type' => $type,
            'amount' => fake()->randomFloat(2, 10, 500),
            'reason' => fake()->sentence(),
            'notes' => fake()->optional()->paragraph(),
            'transaction_date' => fake()->dateTimeBetween('-7 days', 'now'),
        ];
    }

    public function sale(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => CashTransactionType::Sale,
            'pos_sale_id' => PosSale::factory(),
            'reason' => 'POS cash sale',
        ]);
    }

    public function cashIn(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => CashTransactionType::CashIn,
            'pos_sale_id' => null,
            'reason' => fake()->randomElement(['Bank deposit return', 'Petty cash replenishment', 'Customer refund reversal']),
        ]);
    }

    public function cashOut(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => CashTransactionType::CashOut,
            'pos_sale_id' => null,
            'reason' => fake()->randomElement(['Bank deposit', 'Petty cash', 'Supplier payment', 'Refund']),
        ]);
    }

    public function opening(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => CashTransactionType::Opening,
            'pos_sale_id' => null,
            'reason' => 'Cash drawer opened',
        ]);
    }

    public function closing(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => CashTransactionType::Closing,
            'pos_sale_id' => null,
            'reason' => 'Cash drawer closed',
        ]);
    }
}
