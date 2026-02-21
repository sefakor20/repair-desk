<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\DeviceCategory;
use App\Models\DeviceBrand;
use Illuminate\Database\Seeder;

class DeviceBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            // Smartphone brands
            ['name' => 'Apple', 'category' => DeviceCategory::Smartphone],
            ['name' => 'Samsung', 'category' => DeviceCategory::Smartphone],
            ['name' => 'Google', 'category' => DeviceCategory::Smartphone],
            ['name' => 'OnePlus', 'category' => DeviceCategory::Smartphone],
            ['name' => 'Xiaomi', 'category' => DeviceCategory::Smartphone],
            ['name' => 'Oppo', 'category' => DeviceCategory::Smartphone],
            ['name' => 'Vivo', 'category' => DeviceCategory::Smartphone],
            ['name' => 'Huawei', 'category' => DeviceCategory::Smartphone],
            ['name' => 'Motorola', 'category' => DeviceCategory::Smartphone],
            ['name' => 'Nokia', 'category' => DeviceCategory::Smartphone],
            ['name' => 'Infinix', 'category' => DeviceCategory::Smartphone],
            ['name' => 'Tecno', 'category' => DeviceCategory::Smartphone],
            ['name' => 'Itel', 'category' => DeviceCategory::Smartphone],
            ['name' => 'Realme', 'category' => DeviceCategory::Smartphone],

            // Feature Phone brands
            ['name' => 'Nokia', 'category' => DeviceCategory::FeaturePhone],
            ['name' => 'Itel', 'category' => DeviceCategory::FeaturePhone],
            ['name' => 'Tecno', 'category' => DeviceCategory::FeaturePhone],

            // Laptop brands
            ['name' => 'Apple', 'category' => DeviceCategory::Laptop],
            ['name' => 'Dell', 'category' => DeviceCategory::Laptop],
            ['name' => 'HP', 'category' => DeviceCategory::Laptop],
            ['name' => 'Lenovo', 'category' => DeviceCategory::Laptop],
            ['name' => 'Asus', 'category' => DeviceCategory::Laptop],
            ['name' => 'Acer', 'category' => DeviceCategory::Laptop],
            ['name' => 'MSI', 'category' => DeviceCategory::Laptop],
            ['name' => 'Razer', 'category' => DeviceCategory::Laptop],
            ['name' => 'Microsoft', 'category' => DeviceCategory::Laptop],
            ['name' => 'Toshiba', 'category' => DeviceCategory::Laptop],
            ['name' => 'LG', 'category' => DeviceCategory::Laptop],

            // Tablet brands
            ['name' => 'Apple', 'category' => DeviceCategory::Tablet],
            ['name' => 'Samsung', 'category' => DeviceCategory::Tablet],
            ['name' => 'Microsoft', 'category' => DeviceCategory::Tablet],
            ['name' => 'Lenovo', 'category' => DeviceCategory::Tablet],
            ['name' => 'Amazon', 'category' => DeviceCategory::Tablet],

            // Desktop brands
            ['name' => 'Dell', 'category' => DeviceCategory::Desktop],
            ['name' => 'HP', 'category' => DeviceCategory::Desktop],
            ['name' => 'Lenovo', 'category' => DeviceCategory::Desktop],
            ['name' => 'Apple', 'category' => DeviceCategory::Desktop],
            ['name' => 'Asus', 'category' => DeviceCategory::Desktop],
            ['name' => 'Acer', 'category' => DeviceCategory::Desktop],
        ];

        foreach ($brands as $brand) {
            DeviceBrand::firstOrCreate(
                ['name' => $brand['name'], 'category' => $brand['category']],
                ['is_active' => true],
            );
        }
    }
}
