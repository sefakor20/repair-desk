<?php

declare(strict_types=1);

use App\Jobs\ProcessSmsCampaign;
use App\Livewire\Admin\CreateSmsCampaign;
use App\Models\Customer;
use App\Models\SmsCampaign;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

it('renders successfully for admin user', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(CreateSmsCampaign::class)
        ->assertStatus(200);
});

it('requires manage_settings permission', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CreateSmsCampaign::class)
        ->assertStatus(403);
});

it('can create and send campaign immediately', function () {
    Queue::fake();
    $admin = User::factory()->admin()->create();
    Customer::factory()->count(5)->create(['phone' => '+1234567890']);

    Livewire::actingAs($admin)
        ->test(CreateSmsCampaign::class)
        ->set('name', 'Test Campaign')
        ->set('message', 'Hello from our repair shop!')
        ->set('segmentType', 'all')
        ->call('create');

    expect(SmsCampaign::where('name', 'Test Campaign')->exists())->toBeTrue();
    expect(SmsCampaign::where('created_by', $admin->id)->count())->toBe(1);

    Queue::assertPushed(ProcessSmsCampaign::class);
});

it('can create and schedule campaign for later', function () {
    Queue::fake();
    $admin = User::factory()->admin()->create();
    Customer::factory()->count(3)->create(['phone' => '+1234567890']);

    $scheduledDateTime = now()->addHour()->format('Y-m-d H:i:s');

    Livewire::actingAs($admin)
        ->test(CreateSmsCampaign::class)
        ->set('name', 'Scheduled Campaign')
        ->set('message', 'This is a scheduled message!')
        ->set('segmentType', 'all')
        ->set('scheduleForLater', true)
        ->set('scheduledDate', now()->addHour()->format('Y-m-d'))
        ->set('scheduledTime', now()->addHour()->format('H:i'))
        ->call('create');

    $campaign = SmsCampaign::where('name', 'Scheduled Campaign')->first();
    expect($campaign)->not()->toBeNull();
    expect($campaign->status)->toBe('scheduled');
    expect($campaign->created_by)->toBe($admin->id);

    // Should NOT dispatch immediately for scheduled campaigns
    Queue::assertNotPushed(ProcessSmsCampaign::class);
});

it('validates required fields', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(CreateSmsCampaign::class)
        ->call('create')
        ->assertHasErrors(['name', 'message']);
});

it('calculates estimated recipients and cost', function () {
    $admin = User::factory()->admin()->create();
    Customer::factory()->count(10)->create(['phone' => '+1234567890']);

    $component = Livewire::actingAs($admin)
        ->test(CreateSmsCampaign::class)
        ->set('message', 'Test message');

    expect($component->get('estimatedRecipients'))->toBe(10);
    expect($component->get('estimatedCost'))->toBeFloat();
});
