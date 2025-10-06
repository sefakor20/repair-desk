<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\{TicketPriority, TicketStatus};
use App\Models\{Customer, Device, InventoryItem, Invoice, Payment, Ticket, TicketNote, User};
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->admin()->withoutTwoFactor()->create([
            'name' => 'Admin User',
            'email' => 'admin@repairdesk.com',
        ]);

        // Create other staff members
        $manager = User::factory()->manager()->withoutTwoFactor()->create([
            'name' => 'Manager User',
            'email' => 'manager@repairdesk.com',
        ]);

        $technician1 = User::factory()->technician()->withoutTwoFactor()->create([
            'name' => 'John Technician',
            'email' => 'tech1@repairdesk.com',
        ]);

        $technician2 = User::factory()->technician()->withoutTwoFactor()->create([
            'name' => 'Jane Technician',
            'email' => 'tech2@repairdesk.com',
        ]);

        $frontDesk = User::factory()->withoutTwoFactor()->create([
            'name' => 'Front Desk Staff',
            'email' => 'frontdesk@repairdesk.com',
        ]);

        // Create inventory items (parts)
        $inventoryItems = InventoryItem::factory()->count(30)->create();
        InventoryItem::factory()->lowStock()->count(5)->create();
        InventoryItem::factory()->outOfStock()->count(2)->create();

        // Create customers with devices and tickets
        Customer::factory()
            ->count(20)
            ->has(
                Device::factory()
                    ->count(rand(1, 3))
                    ->has(
                        Ticket::factory()
                            ->count(rand(1, 2))
                            ->state(function (array $attributes) use ($technician1, $technician2, $frontDesk) {
                                return [
                                    'assigned_to' => fake()->randomElement([$technician1->id, $technician2->id]),
                                    'created_by' => $frontDesk->id,
                                ];
                            })
                            ->has(
                                TicketNote::factory()
                                    ->count(rand(1, 4))
                                    ->state(function (array $attributes) use ($technician1, $technician2, $frontDesk) {
                                        return [
                                            'user_id' => fake()->randomElement([$technician1->id, $technician2->id, $frontDesk->id]),
                                        ];
                                    }),
                                'notes',
                            ),
                    ),
            )
            ->create();

        // Create some completed tickets with invoices and payments
        $completedTickets = Ticket::factory()
            ->count(10)
            ->state(function (array $attributes) use ($technician1, $technician2, $frontDesk) {
                return [
                    'status' => TicketStatus::Completed,
                    'assigned_to' => fake()->randomElement([$technician1->id, $technician2->id]),
                    'created_by' => $frontDesk->id,
                    'diagnosis' => fake()->sentence(),
                ];
            })
            ->create();

        // Create invoices and payments for completed tickets
        foreach ($completedTickets as $ticket) {
            $invoice = Invoice::factory()
                ->paid()
                ->create([
                    'ticket_id' => $ticket->id,
                    'customer_id' => $ticket->customer_id,
                ]);

            Payment::factory()->create([
                'invoice_id' => $invoice->id,
                'ticket_id' => $ticket->id,
                'amount' => $invoice->total,
                'processed_by' => fake()->randomElement([$frontDesk->id, $manager->id]),
            ]);
        }

        // Create some pending invoices
        $pendingTickets = Ticket::where('status', TicketStatus::Completed)
            ->whereDoesntHave('invoice')
            ->limit(5)
            ->get();

        foreach ($pendingTickets as $ticket) {
            Invoice::factory()
                ->pending()
                ->create([
                    'ticket_id' => $ticket->id,
                    'customer_id' => $ticket->customer_id,
                ]);
        }

        // Create some urgent tickets in progress
        Ticket::factory()
            ->count(3)
            ->state(function (array $attributes) use ($technician1, $technician2, $frontDesk) {
                return [
                    'status' => TicketStatus::InProgress,
                    'priority' => TicketPriority::Urgent,
                    'assigned_to' => fake()->randomElement([$technician1->id, $technician2->id]),
                    'created_by' => $frontDesk->id,
                ];
            })
            ->has(
                TicketNote::factory()
                    ->count(rand(2, 5))
                    ->state(function (array $attributes) use ($technician1, $technician2) {
                        return [
                            'user_id' => fake()->randomElement([$technician1->id, $technician2->id]),
                        ];
                    }),
                'notes',
            )
            ->create();

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin credentials: admin@repairdesk.com / password');
        $this->command->info('Manager credentials: manager@repairdesk.com / password');
        $this->command->info('Technician credentials: tech1@repairdesk.com / password');
    }
}
