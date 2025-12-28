<?php

declare(strict_types=1);

use App\Models\User;

uses()->group('browser', 'smoke');

it('loads main pages without errors', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $pages = visit([
        '/dashboard',
        '/inventory',
        '/analytics',
    ]);

    $pages->assertNoSmoke();
});

it('loads dashboard successfully', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $page = visit('/dashboard');

    $page->assertSee('Dashboard')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

it('loads analytics page', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $page = visit('/analytics');

    $page->assertSee('Sales Analytics')
        ->assertNoJavaScriptErrors();
});
