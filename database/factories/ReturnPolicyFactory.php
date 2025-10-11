<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReturnCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReturnPolicy>
 */
class ReturnPolicyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Standard Return Policy', '30-Day Return', 'Extended Return', 'No Questions Asked']),
            'description' => fake()->sentence(),
            'is_active' => true,
            'return_window_days' => fake()->randomElement([7, 14, 30, 60, 90]),
            'requires_receipt' => fake()->boolean(80),
            'requires_original_packaging' => fake()->boolean(60),
            'requires_approval' => fake()->boolean(30),
            'restocking_fee_percentage' => fake()->randomElement([0, 10, 15, 20]),
            'minimum_restocking_fee' => fake()->randomElement([0, 5, 10, 25]),
            'refund_shipping' => fake()->boolean(40),
            'allowed_conditions' => [ReturnCondition::New->value, ReturnCondition::Opened->value],
            'excluded_categories' => [],
            'terms' => fake()->paragraph(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function lenient(): static
    {
        return $this->state(fn(array $attributes) => [
            'return_window_days' => 90,
            'requires_receipt' => false,
            'requires_original_packaging' => false,
            'requires_approval' => false,
            'restocking_fee_percentage' => 0,
            'minimum_restocking_fee' => 0,
            'allowed_conditions' => [
                ReturnCondition::New->value,
                ReturnCondition::Opened->value,
                ReturnCondition::Used->value,
            ],
        ]);
    }

    public function strict(): static
    {
        return $this->state(fn(array $attributes) => [
            'return_window_days' => 7,
            'requires_receipt' => true,
            'requires_original_packaging' => true,
            'requires_approval' => true,
            'restocking_fee_percentage' => 20,
            'minimum_restocking_fee' => 25,
            'allowed_conditions' => [ReturnCondition::New->value],
        ]);
    }
}
