<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoyaltyTransaction>
 */
class LoyaltyTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(\App\Enums\LoyaltyTransactionType::cases());
        $points = $type->isPositive()
            ? fake()->numberBetween(10, 1000)
            : -fake()->numberBetween(10, 1000);

        return [
            'customer_loyalty_account_id' => \App\Models\CustomerLoyaltyAccount::factory(),
            'type' => $type,
            'points' => $points,
            'balance_after' => fake()->numberBetween(0, 10000),
            'description' => fake()->sentence(),
            'reference_type' => null,
            'reference_id' => null,
            'metadata' => null,
            'expires_at' => null,
        ];
    }

    public function earned(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => \App\Enums\LoyaltyTransactionType::Earned,
            'points' => fake()->numberBetween(10, 1000),
            'description' => 'Points earned from purchase',
        ]);
    }

    public function redeemed(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => \App\Enums\LoyaltyTransactionType::Redeemed,
            'points' => -fake()->numberBetween(100, 5000),
            'description' => 'Points redeemed for reward',
        ]);
    }

    public function bonus(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => \App\Enums\LoyaltyTransactionType::Bonus,
            'points' => fake()->numberBetween(50, 500),
            'description' => 'Bonus points awarded',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => \App\Enums\LoyaltyTransactionType::Expired,
            'points' => -fake()->numberBetween(10, 500),
            'description' => 'Points expired',
            'expires_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    public function adjusted(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => \App\Enums\LoyaltyTransactionType::Adjusted,
            'points' => fake()->randomElement([
                fake()->numberBetween(10, 500),
                -fake()->numberBetween(10, 500),
            ]),
            'description' => 'Manual adjustment by admin',
        ]);
    }

    public function fromPurchase(string $saleId, float $amount): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => \App\Enums\LoyaltyTransactionType::Earned,
            'reference_type' => \App\Models\PosSale::class,
            'reference_id' => $saleId,
            'metadata' => [
                'sale_id' => $saleId,
                'sale_total' => $amount,
            ],
        ]);
    }
}
