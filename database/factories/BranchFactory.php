<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cities = ['Accra', 'Kumasi', 'Tamale', 'Takoradi', 'Cape Coast'];
        $city = fake()->randomElement($cities);

        return [
            'name' => fake()->company() . ' Branch',
            'code' => mb_strtoupper(fake()->unique()->lexify('???')),
            'address' => fake()->streetAddress(),
            'city' => $city,
            'state' => 'Greater Accra',
            'zip' => fake()->postcode(),
            'country' => 'Ghana',
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'is_active' => true,
            'is_main' => false,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function main(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_main' => true,
            'name' => 'Main Branch',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
