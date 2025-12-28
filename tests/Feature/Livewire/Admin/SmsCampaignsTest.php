<?php

declare(strict_types=1);

use App\Livewire\Admin\SmsCampaigns;
use App\Models\SmsCampaign;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully for admin user', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(SmsCampaigns::class)
        ->assertStatus(200);
});

it('requires manage_settings permission', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(SmsCampaigns::class)
        ->assertStatus(403);
});

it('can toggle analytics visibility', function (): void {
    $admin = User::factory()->admin()->create();

    $component = Livewire::actingAs($admin)
        ->test(SmsCampaigns::class);

    // Initially analytics should be hidden
    $component->assertSet('showAnalytics', false);

    // Toggle analytics
    $component->call('toggleAnalytics');
    $component->assertSet('showAnalytics', true);

    // Toggle back
    $component->call('toggleAnalytics');
    $component->assertSet('showAnalytics', false);
});

it('provides accurate analytics data', function (): void {
    $admin = User::factory()->admin()->create();

    // Create test campaigns
    SmsCampaign::factory()->completed()->create([
        'sent_count' => 100,
        'failed_count' => 5,
        'actual_cost' => 12.50,
    ]);

    SmsCampaign::factory()->completed()->create([
        'sent_count' => 50,
        'failed_count' => 2,
        'actual_cost' => 6.25,
    ]);

    $component = Livewire::actingAs($admin)
        ->test(SmsCampaigns::class);

    $analytics = $component->get('analytics');

    expect($analytics)->toHaveKey('total_campaigns');
    expect($analytics)->toHaveKey('total_sent');
    expect($analytics)->toHaveKey('total_failed');
    expect($analytics)->toHaveKey('total_cost');
    expect($analytics['total_sent'])->toBe(150);
    expect($analytics['total_failed'])->toBe(7);
    expect($analytics['total_cost'])->toBe(18.75);
});
