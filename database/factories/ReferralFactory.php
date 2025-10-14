<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\{Customer, Referral};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Referral>
 */
class ReferralFactory extends Factory
{
    protected $model = Referral::class;

    public function definition(): array
    {
        return [
            'referrer_id' => Customer::factory(),
            'referral_code' => mb_strtoupper(fake()->lexify('???')) . fake()->numerify('####'),
            'referred_email' => fake()->unique()->safeEmail(),
            'referred_name' => fake()->name(),
            'status' => fake()->randomElement(['pending', 'completed', 'expired']),
            'points_awarded' => 0,
            'expires_at' => now()->addDays(30),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'pending',
            'referred_id' => null,
            'points_awarded' => 0,
            'completed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'completed',
            'referred_id' => Customer::factory(),
            'points_awarded' => 100,
            'completed_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'expired',
            'expires_at' => now()->subDays(1),
        ]);
    }
}
