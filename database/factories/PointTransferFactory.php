<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\{Customer, PointTransfer};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PointTransfer>
 */
class PointTransferFactory extends Factory
{
    protected $model = PointTransfer::class;

    public function definition(): array
    {
        return [
            'sender_id' => Customer::factory(),
            'recipient_id' => Customer::factory(),
            'points' => fake()->numberBetween(50, 1000),
            'message' => fake()->optional()->sentence(),
            'status' => 'pending',
        ];
    }

    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'pending',
            'completed_at' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'failed',
        ]);
    }
}
