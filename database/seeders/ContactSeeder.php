<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Contact::factory()->count(20)->create();

        // Create a few specific contacts for demonstration
        Contact::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+233241234567',
            'company' => 'Tech Solutions Ghana',
            'position' => 'CEO',
            'is_active' => true,
        ]);

        Contact::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@marketing.com',
            'phone' => '+233501234567',
            'company' => 'Digital Marketing Co',
            'position' => 'Marketing Director',
            'is_active' => true,
        ]);

        Contact::create([
            'first_name' => 'Samuel',
            'last_name' => 'Asante',
            'email' => 'sam@repairs.gh',
            'phone' => '+233261234567',
            'company' => 'Ghana Repairs Ltd',
            'position' => 'Operations Manager',
            'is_active' => true,
        ]);
    }
}
