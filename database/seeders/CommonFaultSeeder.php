<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\DeviceCategory;
use App\Models\CommonFault;
use Illuminate\Database\Seeder;

class CommonFaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faults = [
            // Universal faults (apply to all device types)
            ['name' => 'Water Damage', 'description' => 'Device has been exposed to water or liquid', 'device_category' => null, 'sort_order' => 10],
            ['name' => 'Physical Damage', 'description' => 'Device has physical damage or dents', 'device_category' => null, 'sort_order' => 20],
            ['name' => 'Won\'t Turn On', 'description' => 'Device does not power on', 'device_category' => null, 'sort_order' => 30],
            ['name' => 'Software Issues', 'description' => 'Operating system or software problems', 'device_category' => null, 'sort_order' => 40],

            // Smartphone/Tablet specific faults
            ['name' => 'Cracked Screen', 'description' => 'Display screen is cracked or shattered', 'device_category' => DeviceCategory::Smartphone, 'sort_order' => 50],
            ['name' => 'Charging Port Issue', 'description' => 'Device not charging or charging port damaged', 'device_category' => DeviceCategory::Smartphone, 'sort_order' => 60],
            ['name' => 'Battery Drain', 'description' => 'Battery drains quickly or won\'t hold charge', 'device_category' => DeviceCategory::Smartphone, 'sort_order' => 70],
            ['name' => 'Camera Not Working', 'description' => 'Front or back camera malfunction', 'device_category' => DeviceCategory::Smartphone, 'sort_order' => 80],
            ['name' => 'Touch Screen Unresponsive', 'description' => 'Touch screen not responding to touch', 'device_category' => DeviceCategory::Smartphone, 'sort_order' => 90],
            ['name' => 'Speaker/Microphone Issue', 'description' => 'Audio output or input problems', 'device_category' => DeviceCategory::Smartphone, 'sort_order' => 100],
            ['name' => 'Network/WiFi Issues', 'description' => 'Cannot connect to cellular or WiFi network', 'device_category' => DeviceCategory::Smartphone, 'sort_order' => 110],
            ['name' => 'Back Cover Damaged', 'description' => 'Back panel or cover is broken or cracked', 'device_category' => DeviceCategory::Smartphone, 'sort_order' => 120],
            ['name' => 'Button Not Working', 'description' => 'Power, volume, or home button malfunction', 'device_category' => DeviceCategory::Smartphone, 'sort_order' => 130],

            // Laptop specific faults
            ['name' => 'Keyboard Issues', 'description' => 'Keyboard keys not working or stuck', 'device_category' => DeviceCategory::Laptop, 'sort_order' => 140],
            ['name' => 'Trackpad Not Working', 'description' => 'Touchpad unresponsive or erratic', 'device_category' => DeviceCategory::Laptop, 'sort_order' => 150],
            ['name' => 'Screen Hinge Broken', 'description' => 'Laptop hinge damaged or loose', 'device_category' => DeviceCategory::Laptop, 'sort_order' => 160],
            ['name' => 'No Display Output', 'description' => 'Screen is blank or no video output', 'device_category' => DeviceCategory::Laptop, 'sort_order' => 170],
            ['name' => 'Overheating', 'description' => 'Device gets extremely hot during use', 'device_category' => DeviceCategory::Laptop, 'sort_order' => 180],
            ['name' => 'Hard Drive Failure', 'description' => 'Storage drive not detected or failing', 'device_category' => DeviceCategory::Laptop, 'sort_order' => 190],
            ['name' => 'RAM Issue', 'description' => 'Memory related problems or failures', 'device_category' => DeviceCategory::Laptop, 'sort_order' => 200],
            ['name' => 'Charging Not Working', 'description' => 'Laptop not charging or power adapter issue', 'device_category' => DeviceCategory::Laptop, 'sort_order' => 210],
            ['name' => 'Fan Noise', 'description' => 'Cooling fan making excessive noise', 'device_category' => DeviceCategory::Laptop, 'sort_order' => 220],
            ['name' => 'Ports Not Working', 'description' => 'USB, HDMI, or other ports not functioning', 'device_category' => DeviceCategory::Laptop, 'sort_order' => 230],

            // Desktop specific faults
            ['name' => 'No POST', 'description' => 'Computer does not pass power-on self-test', 'device_category' => DeviceCategory::Desktop, 'sort_order' => 240],
            ['name' => 'Monitor Issues', 'description' => 'Display problems or no video signal', 'device_category' => DeviceCategory::Desktop, 'sort_order' => 250],
            ['name' => 'PSU Failure', 'description' => 'Power supply unit not working', 'device_category' => DeviceCategory::Desktop, 'sort_order' => 260],
            ['name' => 'Motherboard Issue', 'description' => 'Mainboard malfunction or failure', 'device_category' => DeviceCategory::Desktop, 'sort_order' => 270],
            ['name' => 'GPU Problems', 'description' => 'Graphics card not working or artifacts', 'device_category' => DeviceCategory::Desktop, 'sort_order' => 280],
        ];

        foreach ($faults as $fault) {
            CommonFault::firstOrCreate(
                ['name' => $fault['name'], 'device_category' => $fault['device_category']],
                [
                    'description' => $fault['description'],
                    'sort_order' => $fault['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}
