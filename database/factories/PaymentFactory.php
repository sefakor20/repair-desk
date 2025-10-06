<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\{Invoice, Ticket, User};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'ticket_id' => Ticket::factory(),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
            'payment_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'transaction_reference' => fake()->optional()->bothify('REF-####-????'),
            'notes' => fake()->optional()->sentence(),
            'processed_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the payment was made by cash.
     */
    public function cash(): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_method' => PaymentMethod::Cash,
            'transaction_reference' => null,
        ]);
    }

    /**
     * Indicate that the payment was made by card.
     */
    public function card(): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_method' => PaymentMethod::Card,
            'transaction_reference' => fake()->bothify('CARD-####-????'),
        ]);
    }
}
