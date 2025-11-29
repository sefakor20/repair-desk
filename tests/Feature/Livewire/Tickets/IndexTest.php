<?php

declare(strict_types=1);

use App\Models\{Customer, Device, Ticket, User};
use Livewire\Livewire;

use function Pest\Laravel\{actingAs};

beforeEach(function (): void {
    $this->user = createAdmin();
    $this->customer = Customer::factory()->create();
    $this->device = Device::factory()->for($this->customer)->create();
});

test('tickets index page can be rendered', function (): void {
    actingAs($this->user)
        ->get(route('tickets.index'))
        ->assertOk()
        ->assertSee('Tickets')
        ->assertSee('Manage repair tickets and track progress');
});

test('tickets list shows ticket data', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create();

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Index::class)
        ->assertSee($ticket->ticket_number)
        ->assertSee($ticket->customer->full_name)
        ->assertSee($ticket->status->label());
});

test('search filters tickets', function (): void {
    $ticket1 = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['problem_description' => 'Screen is broken']);

    $ticket2 = Ticket::factory()
        ->for(Customer::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']))
        ->for(Device::factory()->create())
        ->create(['problem_description' => 'Battery not charging']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Index::class)
        ->set('search', 'broken')
        ->assertSee($ticket1->ticket_number)
        ->assertDontSee($ticket2->ticket_number)
        ->set('search', 'John')
        ->assertSee($ticket2->ticket_number)
        ->assertDontSee($ticket1->ticket_number);
});

test('status filter works', function (): void {
    $newTicket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->statusNew()
        ->create();

    $inProgressTicket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->inProgress()
        ->create();

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Index::class)
        ->set('statusFilter', 'new')
        ->assertSee($newTicket->ticket_number)
        ->assertDontSee($inProgressTicket->ticket_number)
        ->set('statusFilter', 'in_progress')
        ->assertSee($inProgressTicket->ticket_number)
        ->assertDontSee($newTicket->ticket_number);
});

test('priority filter works', function (): void {
    $urgentTicket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->urgent()
        ->create();

    $lowTicket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->lowPriority()
        ->create();

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Index::class)
        ->set('priorityFilter', 'urgent')
        ->assertSee($urgentTicket->ticket_number)
        ->assertDontSee($lowTicket->ticket_number)
        ->set('priorityFilter', 'low')
        ->assertSee($lowTicket->ticket_number)
        ->assertDontSee($urgentTicket->ticket_number);
});

test('assigned filter works', function (): void {
    $technician = User::factory()->technician()->create();

    $assignedTicket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['assigned_to' => $technician->id]);

    $unassignedTicket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create(['assigned_to' => null]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Index::class)
        ->set('assignedFilter', $technician->id)
        ->assertSee($assignedTicket->ticket_number)
        ->assertDontSee($unassignedTicket->ticket_number)
        ->set('assignedFilter', 'unassigned')
        ->assertSee($unassignedTicket->ticket_number)
        ->assertDontSee($assignedTicket->ticket_number);
});

test('tickets are paginated', function (): void {
    Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->count(20)
        ->create();

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Index::class)
        ->assertSee('Next');
});

test('clear filters works', function (): void {
    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Tickets\Index::class)
        ->set('search', 'test')
        ->set('statusFilter', 'new')
        ->set('priorityFilter', 'urgent')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('statusFilter', '')
        ->assertSet('priorityFilter', '')
        ->assertSet('assignedFilter', '');
});

test('user can delete a ticket', function (): void {
    $ticket = Ticket::factory()
        ->for($this->customer)
        ->for($this->device)
        ->create();

    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Tickets\Index::class)
        ->call('delete', $ticket->id);

    expect(Ticket::find($ticket->id))->toBeNull();
});
