<?php

declare(strict_types=1);

use App\Livewire\CommandPalette;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

test('command palette can be rendered', function (): void {
    Livewire::test(CommandPalette::class)
        ->assertOk();
});

test('command palette is closed by default', function (): void {
    Livewire::test(CommandPalette::class)
        ->assertSet('isOpen', false);
});

test('command palette can be toggled', function (): void {
    Livewire::test(CommandPalette::class)
        ->call('toggle')
        ->assertSet('isOpen', true)
        ->call('toggle')
        ->assertSet('isOpen', false);
});

test('command palette can be closed', function (): void {
    Livewire::test(CommandPalette::class)
        ->set('isOpen', true)
        ->call('close')
        ->assertSet('isOpen', false);
});

test('command palette displays navigation commands', function (): void {
    Livewire::test(CommandPalette::class)
        ->assertSee('Dashboard')
        ->assertSee('Customers')
        ->assertSee('Tickets')
        ->assertSee('Inventory')
        ->assertSee('Invoices');
});

test('command palette displays create commands', function (): void {
    Livewire::test(CommandPalette::class)
        ->assertSee('New Customer')
        ->assertSee('New Device')
        ->assertSee('New Ticket')
        ->assertSee('New Invoice');
});

test('command palette filters commands by query', function (): void {
    Livewire::test(CommandPalette::class)
        ->set('query', 'customer')
        ->assertSee('Customers')
        ->assertSee('New Customer')
        ->assertDontSee('Tickets')
        ->assertDontSee('Inventory');
});

test('command palette searches in descriptions', function (): void {
    Livewire::test(CommandPalette::class)
        ->set('query', 'repair')
        ->assertSee('Tickets')
        ->assertSee('New Ticket');
});

test('command palette searches in keywords', function (): void {
    Livewire::test(CommandPalette::class)
        ->set('query', 'stock')
        ->assertSee('Inventory');
});

test('command palette shows no results for invalid query', function (): void {
    Livewire::test(CommandPalette::class)
        ->set('query', 'xyzabc123')
        ->assertSee('No commands found');
});

test('command palette can select next command', function (): void {
    Livewire::test(CommandPalette::class)
        ->assertSet('selectedIndex', 0)
        ->call('selectNext')
        ->assertSet('selectedIndex', 1);
});

test('command palette can select previous command', function (): void {
    Livewire::test(CommandPalette::class)
        ->set('selectedIndex', 1)
        ->call('selectPrevious')
        ->assertSet('selectedIndex', 0);
});

test('command palette wraps selection at end', function (): void {
    $component = Livewire::test(CommandPalette::class);
    $commandCount = count($component->get('commands'));

    $component
        ->set('selectedIndex', $commandCount - 1)
        ->call('selectNext')
        ->assertSet('selectedIndex', 0);
});

test('command palette resets selection when query changes', function (): void {
    Livewire::test(CommandPalette::class)
        ->set('selectedIndex', 5)
        ->set('query', 'customer')
        ->assertSet('selectedIndex', 0);
});

test('command palette shows admin commands for admin users', function (): void {
    $admin = User::factory()->admin()->create();

    actingAs($admin);

    Livewire::test(CommandPalette::class)
        ->assertSee('Reports')
        ->assertSee('Users')
        ->assertSee('New User');
});

test('command palette hides admin commands for non-admin users', function (): void {
    $technician = User::factory()->technician()->create();

    actingAs($technician);

    Livewire::test(CommandPalette::class)
        ->assertDontSee('Users')
        ->assertDontSee('New User');
});

test('command palette closes after executing command', function (): void {
    Livewire::test(CommandPalette::class)
        ->set('isOpen', true)
        ->call('executeByIndex', 0)
        ->assertSet('isOpen', false);
});
