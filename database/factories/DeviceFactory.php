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

        $colors = ['Black', 'White', 'Silver', 'Gold', 'Blue', 'Red', 'Green', 'Purple'];
        $storageOptions = ['64GB', '128GB', '256GB', '512GB', '1TB', '2TB'];
        $conditions = ['excellent', 'good', 'fair', 'poor', 'damaged'];

        $type = fake()->randomElement($types);
        $brand = fake()->randomElement($brands[$type]);
        $purchaseDate = fake()->optional()->dateTimeBetween('-3 years', 'now');

        return [
            'customer_id' => Customer::factory(),
            'type' => $type,
            'brand' => $brand,
            'model' => fake()->bothify('Model-###??'),
            'color' => fake()->optional()->randomElement($colors),
            'storage_capacity' => in_array($type, ['Smartphone', 'Tablet', 'Laptop'])
                ? fake()->optional()->randomElement($storageOptions)
                : null,
            'serial_number' => fake()->optional()->bothify('SN-####-????-####'),
            'imei' => $type === 'Smartphone' ? fake()->numerify('###############') : null,
            'notes' => fake()->optional()->sentence(),
            'condition' => fake()->optional()->randomElement($conditions),
            'condition_notes' => fake()->optional()->sentence(),
            'purchase_date' => $purchaseDate,
            'warranty_expiry' => $purchaseDate
                ? fake()->optional()->dateTimeBetween($purchaseDate, '+2 years')
                : null,
            'warranty_provider' => fake()->optional()->company(),
            'warranty_notes' => fake()->optional()->sentence(),
        ];
    }
}
