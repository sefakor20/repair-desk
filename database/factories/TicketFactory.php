<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\{TicketPriority, TicketStatus};
use App\Models\{Customer, Device, User};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $problems = [
            'Screen not responding to touch',
            'Battery drains quickly',
            'Device won\'t turn on',
            'Cracked screen replacement needed',
            'Water damage - won\'t charge',
            'Software update failed',
            'Camera not working',
            'Speaker producing distorted sound',
            'Overheating issues',
            'WiFi connectivity problems',
        ];

        return [
            'customer_id' => Customer::factory(),
            'device_id' => Device::factory(),
            'problem_description' => fake()->randomElement($problems),
            'diagnosis' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(TicketStatus::cases()),
            'priority' => fake()->randomElement(TicketPriority::cases()),
            'estimated_completion' => fake()->optional()->dateTimeBetween('now', '+2 weeks'),
            'assigned_to' => User::factory(),
            'created_by' => User::factory(),
        ];
    }
}
