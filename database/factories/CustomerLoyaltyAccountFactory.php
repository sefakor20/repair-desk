<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerLoyaltyAccount>
 */
class CustomerLoyaltyAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => \App\Models\Customer::factory(),
            'loyalty_tier_id' => \App\Models\LoyaltyTier::factory(),
            'total_points' => fake()->numberBetween(0, 50000),
            'lifetime_points' => fake()->numberBetween(0, 100000),
            'enrolled_at' => fake()->dateTimeBetween('-2 years'),
            'tier_achieved_at' => fake()->dateTimeBetween('-1 year'),
        ];
    }

    public function newMember(): static
    {
        return $this->state(fn(array $attributes) => [
            'total_points' => 0,
            'lifetime_points' => 0,
            'loyalty_tier_id' => null,
            'tier_achieved_at' => null,
            'enrolled_at' => now(),
        ]);
    }

    public function withPoints(int $points): static
    {
        return $this->state(fn(array $attributes) => [
            'total_points' => $points,
            'lifetime_points' => $points,
        ]);
    }

    public function bronze(): static
    {
        return $this->state(fn(array $attributes) => [
            'total_points' => fake()->numberBetween(100, 999),
            'lifetime_points' => fake()->numberBetween(100, 2000),
        ]);
    }

    public function silver(): static
    {
        return $this->state(fn(array $attributes) => [
            'total_points' => fake()->numberBetween(1000, 4999),
            'lifetime_points' => fake()->numberBetween(1000, 10000),
        ]);
    }

    public function gold(): static
    {
        return $this->state(fn(array $attributes) => [
            'total_points' => fake()->numberBetween(5000, 14999),
            'lifetime_points' => fake()->numberBetween(5000, 30000),
        ]);
    }

    public function platinum(): static
    {
        return $this->state(fn(array $attributes) => [
            'total_points' => fake()->numberBetween(15000, 50000),
            'lifetime_points' => fake()->numberBetween(15000, 100000),
        ]);
    }
}
