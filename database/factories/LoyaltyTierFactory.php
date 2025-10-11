<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoyaltyTier>
 */
class LoyaltyTierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond']),
            'description' => fake()->sentence(),
            'min_points' => fake()->numberBetween(0, 10000),
            'points_multiplier' => fake()->randomFloat(2, 1.0, 3.0),
            'discount_percentage' => fake()->randomFloat(2, 0, 20),
            'color' => fake()->hexColor(),
            'priority' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }

    public function bronze(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Bronze',
            'description' => 'Entry level tier for new customers',
            'min_points' => 0,
            'points_multiplier' => 1.0,
            'discount_percentage' => 0,
            'color' => '#CD7F32',
            'priority' => 1,
        ]);
    }

    public function silver(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Silver',
            'description' => 'Intermediate tier with enhanced benefits',
            'min_points' => 1000,
            'points_multiplier' => 1.25,
            'discount_percentage' => 5,
            'color' => '#C0C0C0',
            'priority' => 2,
        ]);
    }

    public function gold(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Gold',
            'description' => 'Premium tier with great rewards',
            'min_points' => 5000,
            'points_multiplier' => 1.5,
            'discount_percentage' => 10,
            'color' => '#FFD700',
            'priority' => 3,
        ]);
    }

    public function platinum(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Platinum',
            'description' => 'Elite tier for our best customers',
            'min_points' => 15000,
            'points_multiplier' => 2.0,
            'discount_percentage' => 15,
            'color' => '#E5E4E2',
            'priority' => 4,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
