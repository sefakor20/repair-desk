<?php

declare(strict_types=1);

use App\Livewire\Admin\CreateSmsCampaign;
use App\Models\Contact;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('contact selection immediately updates recipient count', function () {
    // Create test contacts
    $contact1 = Contact::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '+233501234567',
        'is_active' => true,
    ]);

    $contact2 = Contact::factory()->create([
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'phone' => '+233501234568',
        'is_active' => true,
    ]);

    $component = Livewire::test(CreateSmsCampaign::class)
        ->set('name', 'Test Campaign')
        ->set('message', 'Test message')
        ->set('segmentType', 'contacts');

    // Initially no contacts selected, should have 0 recipients
    $component->assertSet('estimatedRecipients', 0);

    // Select first contact
    $component->set('selectedContactIds', [$contact1->id]);
    $component->assertSet('estimatedRecipients', 1);

    // Select second contact
    $component->set('selectedContactIds', [$contact1->id, $contact2->id]);
    $component->assertSet('estimatedRecipients', 2);

    // Deselect first contact
    $component->set('selectedContactIds', [$contact2->id]);
    $component->assertSet('estimatedRecipients', 1);

    // Deselect all
    $component->set('selectedContactIds', []);
    $component->assertSet('estimatedRecipients', 0);
});

test('estimated cost updates with contact selection', function () {
    $contact1 = Contact::factory()->create([
        'phone' => '+233501234567',
        'is_active' => true,
    ]);

    $contact2 = Contact::factory()->create([
        'phone' => '+233501234568',
        'is_active' => true,
    ]);

    $component = Livewire::test(CreateSmsCampaign::class)
        ->set('name', 'Test Campaign')
        ->set('message', 'Test message') // Should be 1 segment
        ->set('segmentType', 'contacts');

    // Initially no contacts selected, should have null cost
    $component->assertSet('estimatedCost', null);

    // Select first contact - should calculate cost
    $component->set('selectedContactIds', [$contact1->id]);
    $component->assertSet('estimatedRecipients', 1);

    // Cost should be calculated (1 recipient × 1 segment × cost_per_segment)
    $expectedCost = 1 * 1 * config('services.texttango.cost_per_segment', 0.12);
    $component->assertSet('estimatedCost', $expectedCost);

    // Select second contact - should double the cost
    $component->set('selectedContactIds', [$contact1->id, $contact2->id]);
    $component->assertSet('estimatedRecipients', 2);

    $expectedCost = 2 * 1 * config('services.texttango.cost_per_segment', 0.12);
    $component->assertSet('estimatedCost', $expectedCost);
});

test('switching segment types resets contact selection', function () {
    $contact = Contact::factory()->create([
        'phone' => '+233501234567',
        'is_active' => true,
    ]);

    $component = Livewire::test(CreateSmsCampaign::class)
        ->set('name', 'Test Campaign')
        ->set('message', 'Test message')
        ->set('segmentType', 'contacts')
        ->set('selectedContactIds', [$contact->id]);

    // Should have 1 recipient from contact selection
    $component->assertSet('estimatedRecipients', 1);

    // Switch to 'all' segment type
    $component->set('segmentType', 'all');

    // Should recalculate based on all customers, not contacts
    $component->call('calculateEstimate');

    // The estimate should be based on Customer count, not the selected contacts
    expect($component->get('estimatedRecipients'))->not->toBe(1);
});
