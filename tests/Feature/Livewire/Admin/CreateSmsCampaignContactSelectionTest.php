<?php

declare(strict_types=1);

use App\Livewire\Admin\CreateSmsCampaign;
use App\Models\Contact;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->actingAs(User::factory()->admin()->create());
});

test('contact selection immediately updates recipient count', function (): void {
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

test('estimated cost updates with contact selection', function (): void {
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
    $expectedCost = 1 * config('services.texttango.cost_per_segment', 0.12);
    $component->assertSet('estimatedCost', $expectedCost);

    // Select second contact - should double the cost
    $component->set('selectedContactIds', [$contact1->id, $contact2->id]);
    $component->assertSet('estimatedRecipients', 2);

    $expectedCost = 2 * config('services.texttango.cost_per_segment', 0.12);
    $component->assertSet('estimatedCost', $expectedCost);
});

test('switching segment types resets contact selection', function (): void {
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
test('preview modal shows and hides correctly', function (): void {
    $component = Livewire::test(CreateSmsCampaign::class)
        ->set('message', 'Test preview message');

    // Initially preview should be hidden
    $component->assertSet('showPreview', false);

    // Show preview
    $component->call('showPreviewModal');
    $component->assertSet('showPreview', true);
    $component->assertSet('previewMessage', 'Test preview message');

    // Close preview
    $component->call('closePreview');
    $component->assertSet('showPreview', false);
});

test('test send modal works correctly', function (): void {
    $component = Livewire::test(CreateSmsCampaign::class)
        ->set('message', 'Test send message');

    // Initially test send should be hidden
    $component->assertSet('showTestSend', false);

    // Show test send modal
    $component->call('showTestSendModal');
    $component->assertSet('showTestSend', true);
    $component->assertSet('previewMessage', 'Test send message');

    // Close test send
    $component->call('closeTestSend');
    $component->assertSet('showTestSend', false);
    $component->assertSet('testPhoneNumber', '');
});

test('test send validates phone number', function (): void {
    $component = Livewire::test(CreateSmsCampaign::class)
        ->set('message', 'Test message')
        ->set('testPhoneNumber', 'invalid-phone');

    $component->call('sendTest');
    $component->assertHasErrors(['testPhoneNumber']);
});

test('template selection works correctly', function (): void {
    $component = Livewire::test(CreateSmsCampaign::class);

    // Initially no template selected
    $component->assertSet('selectedTemplate', '');
    $component->assertSet('message', '');

    // Select a template
    $component->set('selectedTemplate', 'repair_completed');
    $component->call('selectTemplate');

    $expectedMessage = 'Repair Completed - Good news {customer_name}! Your {device} repair is complete. You can pick it up anytime during business hours.';
    $component->assertSet('message', $expectedMessage);
});

test('template can be cleared', function (): void {
    $component = Livewire::test(CreateSmsCampaign::class)
        ->set('selectedTemplate', 'repair_completed')
        ->set('message', 'Some message');

    $component->call('clearTemplate');

    $component->assertSet('selectedTemplate', '');
    $component->assertSet('message', '');
});

test('available templates property returns correct templates', function (): void {
    $component = Livewire::test(CreateSmsCampaign::class);

    $templates = $component->get('availableTemplates');

    expect($templates)->toHaveKey('repair_completed');
    expect($templates)->toHaveKey('appointment_reminder');
    expect($templates['repair_completed'])->toContain('{customer_name}');
});

test('enhanced segmentation options work correctly', function (): void {
    $component = Livewire::test(CreateSmsCampaign::class);

    // Test high-value customer segmentation
    $component->set('segmentType', 'high_value')
        ->set('minSpent', 100.00);

    $component->call('calculateEstimate');
    $component->assertSet('estimatedRecipients', 0); // No customers match this criteria in tests

    // Test frequent customer segmentation
    $component->set('segmentType', 'frequent_customers')
        ->set('minTickets', 3);

    $component->call('calculateEstimate');
    $component->assertSet('estimatedRecipients', 0); // No customers match this criteria in tests
});

test('validation works for enhanced segmentation fields', function (): void {
    $component = Livewire::test(CreateSmsCampaign::class)
        ->set('name', 'Test Campaign')
        ->set('message', 'Test message')
        ->set('segmentType', 'high_value')
        ->set('minSpent', -10); // Invalid negative value

    $component->call('create');
    $component->assertHasErrors(['minSpent']);

    // Test frequent customers validation
    $component->set('segmentType', 'frequent_customers')
        ->set('minTickets', 0); // Invalid zero value

    $component->call('create');
    $component->assertHasErrors(['minTickets']);
});
