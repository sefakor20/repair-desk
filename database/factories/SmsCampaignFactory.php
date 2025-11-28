<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SmsCampaign>
 */
class SmsCampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'message' => fake()->paragraph(),
            'status' => 'draft',
            'segment_rules' => null,
            'scheduled_at' => null,
            'started_at' => null,
            'completed_at' => null,
            'total_recipients' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
            'estimated_cost' => null,
            'actual_cost' => null,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the campaign is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'scheduled',
            'scheduled_at' => fake()->dateTimeBetween('now', '+1 week'),
            'total_recipients' => fake()->numberBetween(10, 100),
            'estimated_cost' => fake()->randomFloat(4, 5, 50),
        ]);
    }

    /**
     * Indicate that the campaign is currently sending.
     */
    public function sending(): static
    {
        $total = fake()->numberBetween(50, 200);
        $sent = fake()->numberBetween(1, $total - 1);

        return $this->state(fn(array $attributes) => [
            'status' => 'sending',
            'started_at' => fake()->dateTimeBetween('-1 hour', 'now'),
            'total_recipients' => $total,
            'sent_count' => $sent,
            'failed_count' => fake()->numberBetween(0, 5),
            'estimated_cost' => fake()->randomFloat(4, 10, 100),
        ]);
    }

    /**
     * Indicate that the campaign is completed.
     */
    public function completed(): static
    {
        $total = fake()->numberBetween(50, 200);
        $sent = fake()->numberBetween($total - 10, $total);
        $failed = $total - $sent;

        return $this->state(fn(array $attributes) => [
            'status' => 'completed',
            'started_at' => fake()->dateTimeBetween('-1 week', '-1 day'),
            'completed_at' => fake()->dateTimeBetween('-1 day', 'now'),
            'total_recipients' => $total,
            'sent_count' => $sent,
            'failed_count' => $failed,
            'estimated_cost' => fake()->randomFloat(4, 10, 100),
            'actual_cost' => fake()->randomFloat(4, 10, 100),
        ]);
    }

    /**
     * Indicate that the campaign is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'cancelled',
            'total_recipients' => fake()->numberBetween(10, 100),
            'estimated_cost' => fake()->randomFloat(4, 5, 50),
        ]);
    }

    /**
     * Indicate that the campaign targets all customers.
     */
    public function allCustomers(): static
    {
        return $this->state(fn(array $attributes) => [
            'segment_rules' => ['type' => 'all'],
        ]);
    }

    /**
     * Indicate that the campaign targets recent customers.
     */
    public function recentCustomers(): static
    {
        return $this->state(fn(array $attributes) => [
            'segment_rules' => [
                'type' => 'recent',
                'days' => fake()->numberBetween(7, 90),
            ],
        ]);
    }
}
