<?php

declare(strict_types=1);

use App\Livewire\Portal\Tickets\Show;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Show::class)
        ->assertStatus(200);
});
