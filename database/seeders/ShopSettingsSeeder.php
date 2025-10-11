<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ShopSettings;
use Illuminate\Database\Seeder;

class ShopSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ShopSettings::updateOrCreate(
            ['id' => 1],
            [
                'shop_name' => 'Repair Desk',
                'address' => '123 Main Street',
                'city' => 'San Francisco',
                'state' => 'CA',
                'zip' => '94102',
                'country' => 'USA',
                'phone' => '(555) 123-4567',
                'email' => 'info@repairdesk.com',
                'website' => 'https://repairdesk.com',
                'tax_rate' => 8.5,
                'currency' => 'USD',
            ],
        );
    }
}
