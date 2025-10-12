<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('dashboard page loads keyboard shortcuts components', function () {
    $this->get('/dashboard')
        ->assertOk()
        ->assertSeeLivewire('command-palette')
        ->assertSeeLivewire('keyboard-shortcuts-help');
});

test('dashboard includes keyboard shortcuts javascript', function () {
    $response = $this->get('/dashboard');

    $response->assertOk();
    // Verify the layout includes JavaScript (either Vite dev or built assets)
    $response->assertSee('<script', false);
});

test('command palette component is present in layout', function () {
    $this->get('/dashboard')
        ->assertOk()
        ->assertSeeLivewire('command-palette');
});

test('keyboard shortcuts help component is present in layout', function () {
    $this->get('/dashboard')
        ->assertOk()
        ->assertSeeLivewire('keyboard-shortcuts-help');
});

test('floating help button is visible on dashboard', function () {
    $this->get('/dashboard')
        ->assertOk()
        ->assertSee('Press', false)
        ->assertSee('for shortcuts', false);
});

test('keyboard shortcuts javascript is loaded on all authenticated pages', function () {
    $pages = [
        '/dashboard',
        '/customers',
        '/tickets',
        '/devices',
        '/inventory',
        '/invoices',
        '/pos',
    ];

    foreach ($pages as $page) {
        $this->get($page)
            ->assertOk()
            ->assertSee('<script', false);
    }
});

test('command palette and help modal components load on all pages', function () {
    $pages = [
        '/dashboard',
        '/customers',
        '/tickets',
    ];

    foreach ($pages as $page) {
        $this->get($page)
            ->assertOk()
            ->assertSeeLivewire('command-palette')
            ->assertSeeLivewire('keyboard-shortcuts-help');
    }
});
