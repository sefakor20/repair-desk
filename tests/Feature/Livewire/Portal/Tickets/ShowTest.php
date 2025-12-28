<?php

declare(strict_types=1);

use App\Livewire\Portal\Tickets\Show;
use App\Models\{Customer, Ticket};
use Livewire\Livewire;

it('renders successfully', function (): void {
    $customer = Customer::factory()->create();
    $ticket = Ticket::factory()->for($customer)->create();

    Livewire::test(Show::class, ['customer' => $customer, 'ticket' => $ticket])
        ->assertStatus(200);
});
