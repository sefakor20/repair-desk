<?php

declare(strict_types=1);

use App\Livewire\Portal\Settings\Preferences;
use App\Models\{Customer, CustomerPreference};
use Livewire\Livewire;

use function Pest\Laravel\{assertDatabaseHas};

it('renders successfully', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Preferences::class, ['customer' => $customer])
        ->assertStatus(200);
});

it('creates preferences with defaults if not exists', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Preferences::class, ['customer' => $customer])
        ->assertSet('notify_points_earned', true)
        ->assertSet('notify_reward_available', true)
        ->assertSet('notify_tier_upgrade', true)
        ->assertSet('notify_points_expiring', true)
        ->assertSet('notify_referral_success', true)
        ->assertSet('marketing_emails', false)
        ->assertSet('newsletter', false);

    expect($customer->preferences)->not->toBeNull();
});

it('loads existing preferences', function () {
    $customer = Customer::factory()->create();
    CustomerPreference::create([
        'customer_id' => $customer->id,
        'notify_points_earned' => false,
        'notify_reward_available' => false,
        'notify_tier_upgrade' => true,
        'notify_points_expiring' => true,
        'notify_referral_success' => false,
        'marketing_emails' => true,
        'newsletter' => true,
    ]);

    Livewire::test(Preferences::class, ['customer' => $customer])
        ->assertSet('notify_points_earned', false)
        ->assertSet('notify_reward_available', false)
        ->assertSet('notify_tier_upgrade', true)
        ->assertSet('notify_points_expiring', true)
        ->assertSet('notify_referral_success', false)
        ->assertSet('marketing_emails', true)
        ->assertSet('newsletter', true);
});

it('successfully updates all preferences', function () {
    $customer = Customer::factory()->create();
    CustomerPreference::create([
        'customer_id' => $customer->id,
        'notify_points_earned' => true,
        'marketing_emails' => false,
    ]);

    Livewire::test(Preferences::class, ['customer' => $customer])
        ->set('notify_points_earned', false)
        ->set('marketing_emails', true)
        ->set('newsletter', true)
        ->call('save')
        ->assertDispatched('toast');

    assertDatabaseHas('customer_preferences', [
        'customer_id' => $customer->id,
        'notify_points_earned' => false,
        'marketing_emails' => true,
        'newsletter' => true,
    ]);
});

it('can opt out of all notifications', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Preferences::class, ['customer' => $customer])
        ->set('notify_points_earned', false)
        ->set('notify_reward_available', false)
        ->set('notify_tier_upgrade', false)
        ->set('notify_points_expiring', false)
        ->set('notify_referral_success', false)
        ->set('marketing_emails', false)
        ->set('newsletter', false)
        ->call('save')
        ->assertHasNoErrors();

    $preferences = $customer->preferences;
    expect($preferences->notify_points_earned)->toBeFalse()
        ->and($preferences->notify_reward_available)->toBeFalse()
        ->and($preferences->marketing_emails)->toBeFalse();
});
