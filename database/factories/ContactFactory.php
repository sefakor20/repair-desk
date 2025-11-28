<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companies = ['TechCorp', 'Mobile Solutions', 'Gadget Plus', 'Electronics Hub', 'Digital Ghana', 'Phone Paradise'];
        $positions = ['Manager', 'Sales Rep', 'Owner', 'Director', 'Technician', 'Customer Service'];

        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->optional(0.8)->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'company' => fake()->optional(0.6)->randomElement($companies),
            'position' => fake()->optional(0.5)->randomElement($positions),
            'address' => fake()->optional(0.7)->address(),
            'notes' => fake()->optional(0.3)->sentence(),
            'tags' => fake()->optional(0.4)->randomElements(['Supplier', 'Partner', 'Lead', 'VIP', 'B2B'], fake()->numberBetween(0, 2)),
            'is_active' => fake()->boolean(90), // 90% active
            'last_contacted_at' => fake()->optional(0.6)->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Indicate that the contact is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the contact has no company.
     */
    public function individual(): static
    {
        return $this->state(fn(array $attributes) => [
            'company' => null,
            'position' => null,
        ]);
    }
}
