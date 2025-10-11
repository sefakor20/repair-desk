<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoyaltyReward>
 */
class LoyaltyRewardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(\App\Enums\LoyaltyRewardType::cases());

        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'type' => $type,
            'points_required' => fake()->numberBetween(100, 10000),
            'reward_value' => $this->getRewardValueForType($type),
            'min_tier_id' => null,
            'valid_from' => null,
            'valid_until' => null,
            'redemption_limit' => null,
            'times_redeemed' => 0,
            'is_active' => true,
        ];
    }

    protected function getRewardValueForType(\App\Enums\LoyaltyRewardType $type): array
    {
        return match ($type) {
            \App\Enums\LoyaltyRewardType::Discount => [
                'percentage' => fake()->numberBetween(5, 50),
                'max_amount' => fake()->numberBetween(10, 100),
            ],
            \App\Enums\LoyaltyRewardType::FreeProduct => [
                'product_id' => fake()->uuid(),
                'quantity' => 1,
            ],
            \App\Enums\LoyaltyRewardType::FreeService => [
                'service_type' => fake()->word(),
                'description' => fake()->sentence(),
            ],
            \App\Enums\LoyaltyRewardType::Voucher => [
                'amount' => fake()->numberBetween(10, 200),
                'code' => fake()->unique()->bothify('VOUCHER-###???'),
            ],
            \App\Enums\LoyaltyRewardType::Custom => [
                'description' => fake()->sentence(),
                'terms' => fake()->paragraph(),
            ],
        };
    }

    public function discount(int $percentage = 10): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => "{$percentage}% Discount Reward",
            'type' => \App\Enums\LoyaltyRewardType::Discount,
            'reward_value' => [
                'percentage' => $percentage,
                'max_amount' => $percentage * 10,
            ],
        ]);
    }

    public function freeProduct(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Free Product Reward',
            'type' => \App\Enums\LoyaltyRewardType::FreeProduct,
            'reward_value' => [
                'product_id' => fake()->uuid(),
                'quantity' => 1,
            ],
        ]);
    }

    public function voucher(int $amount = 50): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => "GHS {$amount} Voucher",
            'type' => \App\Enums\LoyaltyRewardType::Voucher,
            'reward_value' => [
                'amount' => $amount,
                'code' => fake()->unique()->bothify('VOUCHER-###???'),
            ],
        ]);
    }

    public function tierRestricted(): static
    {
        return $this->state(fn(array $attributes) => [
            'min_tier_id' => \App\Models\LoyaltyTier::factory(),
        ]);
    }

    public function timeLimited(): static
    {
        return $this->state(fn(array $attributes) => [
            'valid_from' => now(),
            'valid_until' => now()->addMonths(3),
        ]);
    }

    public function limitedQuantity(int $limit = 100): static
    {
        return $this->state(fn(array $attributes) => [
            'redemption_limit' => $limit,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'valid_from' => now()->subMonths(6),
            'valid_until' => now()->subMonth(),
        ]);
    }
}
