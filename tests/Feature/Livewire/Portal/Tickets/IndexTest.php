<?php

declare(strict_types=1);

use App\Livewire\Portal\Tickets\Index;
use App\Models\Customer;
use Livewire\Livewire;

it('renders successfully', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Index::class, ['customer' => $customer])
        ->assertStatus(200);
});
