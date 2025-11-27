<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\{Customer, SmsDeliveryLog};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SmsDeliveryLog>
 */
class SmsDeliveryLogFactory extends Factory
{
    protected $model = SmsDeliveryLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['pending', 'sent', 'failed'];
        $status = fake()->randomElement($statuses);

        return [
            'notifiable_type' => Customer::class,
            'notifiable_id' => Customer::factory(),
            'phone' => fake()->phoneNumber(),
            'message' => fake()->sentence(),
            'notification_type' => fake()->randomElement([
                'App\\Notifications\\TicketStatusChanged',
                'App\\Notifications\\RepairCompleted',
                'App\\Notifications\\LowStockAlert',
            ]),
            'status' => $status,
            'error_message' => $status === 'failed' ? fake()->sentence() : null,
            'response_data' => $status === 'sent' ? ['success' => true, 'id' => fake()->uuid()] : null,
            'sent_at' => $status === 'sent' ? fake()->dateTimeBetween('-1 week', 'now') : null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'pending',
            'sent_at' => null,
            'error_message' => null,
            'response_data' => null,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'sent',
            'sent_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'error_message' => null,
            'response_data' => ['success' => true, 'id' => fake()->uuid()],
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'failed',
            'sent_at' => null,
            'error_message' => fake()->sentence(),
            'response_data' => null,
        ]);
    }
}
