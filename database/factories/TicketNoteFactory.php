<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\{Ticket, User};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketNote>
 */
class TicketNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
            'note' => fake()->paragraph(),
            'is_internal' => fake()->boolean(30),
        ];
    }

    /**
     * Indicate that the note is internal only.
     */
    public function internal(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_internal' => true,
        ]);
    }

    /**
     * Indicate that the note is visible to customers.
     */
    public function public(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_internal' => false,
        ]);
    }
}
