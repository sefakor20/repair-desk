<?php

declare(strict_types=1);

use App\Livewire\Portal\Auth\Login;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Login::class)
        ->assertStatus(200);
});
