<?php

declare(strict_types=1);

use App\Livewire\KeyboardShortcutsHelp;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

test('keyboard shortcuts help can be rendered', function (): void {
    Livewire::test(KeyboardShortcutsHelp::class)
        ->assertOk();
});

test('keyboard shortcuts help is closed by default', function (): void {
    Livewire::test(KeyboardShortcutsHelp::class)
        ->assertSet('isOpen', false);
});

test('keyboard shortcuts help can be toggled', function (): void {
    Livewire::test(KeyboardShortcutsHelp::class)
        ->call('toggle')
        ->assertSet('isOpen', true)
        ->call('toggle')
        ->assertSet('isOpen', false);
});

test('keyboard shortcuts help can be closed', function (): void {
    Livewire::test(KeyboardShortcutsHelp::class)
        ->set('isOpen', true)
        ->call('close')
        ->assertSet('isOpen', false);
});

test('keyboard shortcuts help displays general shortcuts', function (): void {
    Livewire::test(KeyboardShortcutsHelp::class)
        ->assertSee('General')
        ->assertSee('Open command palette')
        ->assertSee('Focus search')
        ->assertSee('Show keyboard shortcuts')
        ->assertSee('Close modals/palettes');
});

test('keyboard shortcuts help displays navigation shortcuts', function (): void {
    Livewire::test(KeyboardShortcutsHelp::class)
        ->assertSee('Navigation')
        ->assertSee('Dashboard')
        ->assertSee('Customers')
        ->assertSee('Tickets')
        ->assertSee('Inventory')
        ->assertSee('Invoices');
});

test('keyboard shortcuts help displays action shortcuts', function (): void {
    Livewire::test(KeyboardShortcutsHelp::class)
        ->assertSee('Actions')
        ->assertSee('Create new item')
        ->assertSee('Edit current item');
});

test('keyboard shortcuts help displays pro tips', function (): void {
    Livewire::test(KeyboardShortcutsHelp::class)
        ->assertSee('Pro Tips')
        ->assertSee('G then D')
        ->assertSee('Ctrl+K')
        ->assertSee('form fields');
});
