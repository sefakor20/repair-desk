<?php

declare(strict_types=1);

use App\Livewire\Portal\Referrals\Index;
use App\Models\{Customer, Referral};
use Livewire\Livewire;

use function Pest\Laravel\{assertDatabaseHas};

it('renders successfully', function (): void {
    $customer = Customer::factory()->create();

    Livewire::test(Index::class, ['customer' => $customer])
        ->assertStatus(200);
});

it('generates referral code on mount if not exists', function (): void {
    $customer = Customer::factory()->create(['referral_code' => null]);

    Livewire::test(Index::class, ['customer' => $customer])
        ->assertSet('referralCode', fn($code): bool => !empty($code));

    expect($customer->fresh()->referral_code)->not->toBeNull();
});

it('uses existing referral code', function (): void {
    $customer = Customer::factory()->create(['referral_code' => 'ABC1234']);

    Livewire::test(Index::class, ['customer' => $customer])
        ->assertSet('referralCode', 'ABC1234');
});

it('displays referral statistics', function (): void {
    $customer = Customer::factory()->create();

    Referral::factory()->count(3)->create([
        'referrer_id' => $customer->id,
        'status' => 'completed',
        'points_awarded' => 100,
    ]);

    Referral::factory()->count(2)->create([
        'referrer_id' => $customer->id,
        'status' => 'pending',
    ]);

    Livewire::test(Index::class, ['customer' => $customer])
        ->assertSee('5') // total
        ->assertSee('3') // completed
        ->assertSee('2') // pending
        ->assertSee('300'); // points earned
});
it('validates friend email is required', function (): void {
    $customer = Customer::factory()->create();

    Livewire::test(Index::class, ['customer' => $customer])
        ->call('openInviteModal')
        ->set('friend_email', '')
        ->call('sendInvite')
        ->assertHasErrors(['friend_email']);
});

it('validates friend email format', function (): void {
    $customer = Customer::factory()->create();

    Livewire::test(Index::class, ['customer' => $customer])
        ->call('openInviteModal')
        ->set('friend_email', 'not-an-email')
        ->call('sendInvite')
        ->assertHasErrors(['friend_email']);
});

it('prevents duplicate referral invitations', function (): void {
    $customer = Customer::factory()->create();
    Referral::create([
        'referrer_id' => $customer->id,
        'referral_code' => $customer->referral_code ?? 'ABC1234',
        'referred_email' => 'friend@example.com',
        'status' => 'pending',
    ]);

    Livewire::test(Index::class, ['customer' => $customer])
        ->call('openInviteModal')
        ->set('friend_email', 'friend@example.com')
        ->call('sendInvite')
        ->assertHasErrors(['friend_email']);
});

it('successfully sends invitation', function (): void {
    $customer = Customer::factory()->create(['referral_code' => 'ABC1234']);

    Livewire::test(Index::class, ['customer' => $customer])
        ->call('openInviteModal')
        ->set('friend_email', 'friend@example.com')
        ->set('friend_name', 'John Friend')
        ->call('sendInvite')
        ->assertHasNoErrors()
        ->assertDispatched('toast')
        ->assertSet('showInviteModal', false);

    assertDatabaseHas('referrals', [
        'referrer_id' => $customer->id,
        'referred_email' => 'friend@example.com',
        'referred_name' => 'John Friend',
        'status' => 'pending',
    ]);
});

it('can send invitation without friend name', function (): void {
    $customer = Customer::factory()->create(['referral_code' => 'ABC1234']);

    Livewire::test(Index::class, ['customer' => $customer])
        ->call('openInviteModal')
        ->set('friend_email', 'friend@example.com')
        ->call('sendInvite')
        ->assertHasNoErrors();
});

it('displays list of referrals', function (): void {
    $customer = Customer::factory()->create();
    $referredCustomer = Customer::factory()->create(['email' => 'referred@example.com']);

    Referral::create([
        'referrer_id' => $customer->id,
        'referred_id' => $referredCustomer->id,
        'referral_code' => 'ABC1234',
        'referred_email' => 'referred@example.com',
        'status' => 'completed',
        'points_awarded' => 100,
    ]);

    Livewire::test(Index::class, ['customer' => $customer])
        ->assertSee('referred@example.com')
        ->assertSee('Completed');
});

it('opens and closes invite modal', function (): void {
    $customer = Customer::factory()->create();

    Livewire::test(Index::class, ['customer' => $customer])
        ->assertSet('showInviteModal', false)
        ->call('openInviteModal')
        ->assertSet('showInviteModal', true)
        ->call('closeInviteModal')
        ->assertSet('showInviteModal', false);
});
