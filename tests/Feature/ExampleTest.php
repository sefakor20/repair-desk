<?php

declare(strict_types=1);

use App\Models\User;

test('root route redirects authenticated users to dashboard', function (): void {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/');

    $response->assertRedirect('/dashboard');
});

test('root route shows login for guests', function (): void {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSeeLivewire(\App\Livewire\Auth\Login::class);
});
