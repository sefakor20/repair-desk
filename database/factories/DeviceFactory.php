<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['Smartphone', 'Tablet', 'Laptop', 'Desktop', 'Smartwatch', 'Gaming Console'];
        $brands = [
            'Smartphone' => ['Apple', 'Samsung', 'Google', 'OnePlus', 'Xiaomi'],
            'Tablet' => ['Apple', 'Samsung', 'Microsoft', 'Lenovo'],
            'Laptop' => ['Apple', 'Dell', 'HP', 'Lenovo', 'ASUS'],
            'Desktop' => ['Dell', 'HP', 'Lenovo', 'Custom Build'],
            'Smartwatch' => ['Apple', 'Samsung', 'Garmin', 'Fitbit'],
            'Gaming Console' => ['Sony', 'Microsoft', 'Nintendo'],
        ];

        $type = fake()->randomElement($types);
        $brand = fake()->randomElement($brands[$type]);

        return [
            'customer_id' => Customer::factory(),
            'type' => $type,
            'brand' => $brand,
            'model' => fake()->bothify('Model-###??'),
            'serial_number' => fake()->optional()->bothify('SN-####-????-####'),
            'imei' => $type === 'Smartphone' ? fake()->numerify('###############') : null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
