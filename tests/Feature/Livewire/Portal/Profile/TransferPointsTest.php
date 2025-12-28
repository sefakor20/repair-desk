<?php

declare(strict_types=1);

use App\Livewire\Portal\Profile\TransferPoints;
use App\Models\{Customer, CustomerLoyaltyAccount, PointTransfer};
use Livewire\Livewire;

use function Pest\Laravel\{assertDatabaseHas};

// Helper to create loyalty account
function createLoyaltyAccount($customer, $points = 0)
{
    CustomerLoyaltyAccount::factory()->for($customer)->create([
        'total_points' => $points,
        'lifetime_points' => $points,
    ]);
    return $customer->fresh();
}

it('renders successfully', function (): void {
    $customer = Customer::factory()->create();
    createLoyaltyAccount($customer, 1000);

    Livewire::test(TransferPoints::class, ['customer' => $customer])
        ->assertStatus(200);
});

it('validates recipient email is required', function (): void {
    $customer = Customer::factory()->create();
    createLoyaltyAccount($customer, 1000);

    Livewire::test(TransferPoints::class, ['customer' => $customer])
        ->call('openTransferModal')
        ->set('recipient_email', '')
        ->set('points', 100)
        ->call('transfer')
        ->assertHasErrors(['recipient_email']);
});

it('validates recipient email exists', function (): void {
    $customer = Customer::factory()->create();
    createLoyaltyAccount($customer, 1000);

    Livewire::test(TransferPoints::class, ['customer' => $customer])
        ->call('openTransferModal')
        ->set('recipient_email', 'nonexistent@example.com')
        ->set('points', 100)
        ->call('transfer')
        ->assertHasErrors(['recipient_email']);
});

it('validates points are required', function (): void {
    $customer = Customer::factory()->create();
    $recipient = Customer::factory()->create(['email' => 'recipient@example.com']);
    createLoyaltyAccount($customer, 1000);
    createLoyaltyAccount($recipient, 0);

    Livewire::test(TransferPoints::class, ['customer' => $customer])
        ->call('openTransferModal')
        ->set('recipient_email', 'recipient@example.com')
        ->set('points', '')
        ->call('transfer')
        ->assertHasErrors(['points']);
});

it('prevents transfer to self', function (): void {
    $customer = Customer::factory()->create(['email' => 'sender@example.com']);
    createLoyaltyAccount($customer, 1000);

    Livewire::test(TransferPoints::class, ['customer' => $customer])
        ->call('openTransferModal')
        ->set('recipient_email', 'sender@example.com')
        ->set('points', 100)
        ->call('transfer')
        ->assertHasErrors(['recipient_email']);
});

it('validates minimum transfer amount', function (): void {
    $customer = Customer::factory()->create();
    $recipient = Customer::factory()->create(['email' => 'recipient@example.com']);
    createLoyaltyAccount($customer, 1000);
    createLoyaltyAccount($recipient, 0);

    Livewire::test(TransferPoints::class, ['customer' => $customer])
        ->call('openTransferModal')
        ->set('recipient_email', 'recipient@example.com')
        ->set('points', 25)
        ->call('transfer')
        ->assertHasErrors(['points']);
});

it('validates sufficient balance', function (): void {
    $customer = Customer::factory()->create();
    $recipient = Customer::factory()->create(['email' => 'recipient@example.com']);
    createLoyaltyAccount($customer, 100);
    createLoyaltyAccount($recipient, 0);

    Livewire::test(TransferPoints::class, ['customer' => $customer])
        ->call('openTransferModal')
        ->set('recipient_email', 'recipient@example.com')
        ->set('points', 200)
        ->call('transfer')
        ->assertHasErrors(['points']);
});

it('successfully transfers points', function (): void {
    $customer = Customer::factory()->create();
    $recipient = Customer::factory()->create(['email' => 'recipient@example.com']);
    createLoyaltyAccount($customer, 1000);
    createLoyaltyAccount($recipient, 0);

    Livewire::test(TransferPoints::class, ['customer' => $customer])
        ->call('openTransferModal')
        ->set('recipient_email', 'recipient@example.com')
        ->set('points', 100)
        ->set('message', 'Gift for you!')
        ->call('transfer')
        ->assertHasNoErrors()
        ->assertDispatched('toast')
        ->assertSet('showTransferModal', false);

    assertDatabaseHas('point_transfers', [
        'sender_id' => $customer->id,
        'recipient_id' => $recipient->id,
        'points' => 100,
        'message' => 'Gift for you!',
        'status' => 'completed',
    ]);

    expect($customer->fresh()->loyaltyAccount->total_points)->toBe(900)
        ->and($recipient->fresh()->loyaltyAccount->total_points)->toBe(100);
});

it('can transfer without message', function (): void {
    $customer = Customer::factory()->create();
    $recipient = Customer::factory()->create(['email' => 'recipient@example.com']);
    createLoyaltyAccount($customer, 1000);
    createLoyaltyAccount($recipient, 0);

    Livewire::test(TransferPoints::class, ['customer' => $customer])
        ->call('openTransferModal')
        ->set('recipient_email', 'recipient@example.com')
        ->set('points', 100)
        ->call('transfer')
        ->assertHasNoErrors();
});

it('displays transfer history', function (): void {
    $customer = Customer::factory()->create();
    $recipient = Customer::factory()->create();
    createLoyaltyAccount($customer, 1000);
    createLoyaltyAccount($recipient, 0);

    PointTransfer::factory()->create([
        'sender_id' => $customer->id,
        'recipient_id' => $recipient->id,
        'points' => 100,
        'status' => 'completed',
    ]);

    Livewire::test(TransferPoints::class, ['customer' => $customer])
        ->assertSee('100')
        ->assertSee('Completed');
});

it('opens and closes transfer modal', function (): void {
    $customer = Customer::factory()->create();
    createLoyaltyAccount($customer, 1000);

    Livewire::test(TransferPoints::class, ['customer' => $customer])
        ->assertSet('showTransferModal', false)
        ->call('openTransferModal')
        ->assertSet('showTransferModal', true)
        ->call('closeTransferModal')
        ->assertSet('showTransferModal', false);
});
