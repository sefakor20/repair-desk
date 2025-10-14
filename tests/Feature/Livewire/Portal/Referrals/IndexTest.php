<?php

declare(strict_types=1);

use App\Livewire\Portal\Referrals\Index;
use App\Models\{Customer, Referral};
use Livewire\Livewire;

use function Pest\Laravel\{assertDatabaseHas};

it('renders successfully', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Index::class, ['customer' => $customer])
        ->assertStatus(200);
});

it('generates referral code on mount if not exists', function () {
    $customer = Customer::factory()->create(['referral_code' => null]);

    Livewire::test(Index::class, ['customer' => $customer])
        ->assertSet('referralCode', fn($code) => !empty($code));

    expect($customer->fresh()->referral_code)->not->toBeNull();
});

it('uses existing referral code', function () {
    $customer = Customer::factory()->create(['referral_code' => 'ABC1234']);

    Livewire::test(Index::class, ['customer' => $customer])
        ->assertSet('referralCode', 'ABC1234');
});

it('displays referral statistics', function () {
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

    $component = Livewire::test(Index::class, ['customer' => $customer]);
    $stats = $component->get('stats');

    expect($stats['total'])->toBe(5)
        ->and($stats['completed'])->toBe(3)
        ->and($stats['pending'])->toBe(2)
        ->and($stats['points_earned'])->toBe(300);
});

it('validates friend email is required', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Index::class, ['customer' => $customer])
        ->call('openInviteModal')
        ->set('friend_email', '')
        ->call('sendInvite')
        ->assertHasErrors(['friend_email']);
});

it('validates friend email format', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Index::class, ['customer' => $customer])
        ->call('openInviteModal')
        ->set('friend_email', 'not-an-email')
        ->call('sendInvite')
        ->assertHasErrors(['friend_email']);
});

it('prevents duplicate referral invitations', function () {
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

it('successfully sends invitation', function () {
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

it('can send invitation without friend name', function () {
    $customer = Customer::factory()->create(['referral_code' => 'ABC1234']);

    Livewire::test(Index::class, ['customer' => $customer])
        ->call('openInviteModal')
        ->set('friend_email', 'friend@example.com')
        ->call('sendInvite')
        ->assertHasNoErrors();
});

it('displays list of referrals', function () {
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

it('opens and closes invite modal', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Index::class, ['customer' => $customer])
        ->assertSet('showInviteModal', false)
        ->call('openInviteModal')
        ->assertSet('showInviteModal', true)
        ->call('closeInviteModal')
        ->assertSet('showInviteModal', false);
});
