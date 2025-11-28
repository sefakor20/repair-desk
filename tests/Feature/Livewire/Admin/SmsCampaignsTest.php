<?php

declare(strict_types=1);

use App\Livewire\Admin\SmsCampaigns;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully for admin user', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(SmsCampaigns::class)
        ->assertStatus(200);
});

it('requires manage_settings permission', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(SmsCampaigns::class)
        ->assertStatus(403);
});
