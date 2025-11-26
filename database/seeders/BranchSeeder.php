<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Ho',
                'code' => 'HO',
                'address' => '123 Main St',
                'city' => 'Ho',
                'state' => 'Volta',
                'zip' => '00233',
                'country' => 'Ghana',
                'phone' => '+233201234567',
                'email' => 'ho@company.com',
                'is_active' => true,
                'is_main' => true,
                'notes' => null,
            ],
            [
                'name' => 'Accra',
                'code' => 'ACC',
                'address' => '456 Capital Rd',
                'city' => 'Accra',
                'state' => 'Greater Accra',
                'zip' => '00233',
                'country' => 'Ghana',
                'phone' => '+233209876543',
                'email' => 'accra@company.com',
                'is_active' => true,
                'is_main' => false,
                'notes' => null,
            ],
            [
                'name' => 'Dzodze',
                'code' => 'DZ',
                'address' => '789 Border Ave',
                'city' => 'Dzodze',
                'state' => 'Volta',
                'zip' => '00233',
                'country' => 'Ghana',
                'phone' => '+233208765432',
                'email' => 'dzodze@company.com',
                'is_active' => true,
                'is_main' => false,
                'notes' => null,
            ],
        ];

        foreach ($branches as $branch) {
            \App\Models\Branch::updateOrCreate([
                'code' => $branch['code'],
            ], $branch);
        }
    }
}
