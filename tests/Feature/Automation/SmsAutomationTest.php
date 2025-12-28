<?php

declare(strict_types=1);

use App\Jobs\ProcessSmsAutomationTrigger;
use App\Models\Customer;
use App\Models\SmsAutomationTrigger;
use App\Models\SmsTemplate;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->admin = User::factory()->create();
    $this->admin->role = \App\Enums\UserRole::Admin;
    $this->admin->save();

    $this->actingAs($this->admin);
});

test('automation trigger is created successfully', function (): void {
    $template = SmsTemplate::create([
        'name' => 'Status Updated Template',
        'key' => 'status_updated',
        'message' => 'Hello {customer_name}, your ticket {ticket_number} status has been updated to {status}.',
        'is_active' => true,
    ]);

    $trigger = SmsAutomationTrigger::create([
        'name' => 'Status Update Notification',
        'trigger_event' => 'ticket_status_changed',
        'sms_template_id' => $template->id,
        'delay_minutes' => 0,
        'send_to_customer' => true,
        'is_active' => true,
        'created_by' => $this->admin->id,
    ]);

    expect($trigger->exists())->toBeTrue();
    expect($trigger->name)->toBe('Status Update Notification');
    expect($trigger->smsTemplate->id)->toBe($template->id);
});

test('automation trigger fires when ticket status changes', function (): void {
    Queue::fake();

    // Create a template
    $template = SmsTemplate::create([
        'name' => 'Status Updated Template',
        'key' => 'status_updated',
        'message' => 'Hello {customer_name}, your ticket {ticket_number} status has been updated to {status}.',
        'is_active' => true,
    ]);

    // Create an automation trigger
    $trigger = SmsAutomationTrigger::create([
        'name' => 'Status Update Notification',
        'trigger_event' => 'ticket_status_changed',
        'sms_template_id' => $template->id,
        'delay_minutes' => 0,
        'send_to_customer' => true,
        'is_active' => true,
        'created_by' => $this->admin->id,
    ]);

    // Create a customer and ticket
    $customer = Customer::factory()->create([
        'phone' => '+233123456789',
    ]);

    $ticket = Ticket::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'new',
    ]);

    // Update ticket status (this should trigger the automation)
    $ticket->update(['status' => 'in_progress']);

    // Assert that the job was queued
    Queue::assertPushed(ProcessSmsAutomationTrigger::class, function ($job) use ($trigger, $ticket) {
        return $job->trigger->id === $trigger->id && $job->model->id === $ticket->id;
    });
});

test('automation trigger respects delay settings', function (): void {
    Queue::fake();

    $template = SmsTemplate::create([
        'name' => 'Delayed Template',
        'key' => 'delayed',
        'message' => 'This is a delayed message for {customer_name}.',
        'is_active' => true,
    ]);

    $trigger = SmsAutomationTrigger::create([
        'name' => 'Delayed Notification',
        'trigger_event' => 'ticket_status_changed',
        'sms_template_id' => $template->id,
        'delay_minutes' => 30, // 30 minute delay
        'send_to_customer' => true,
        'is_active' => true,
        'created_by' => $this->admin->id,
    ]);

    $customer = Customer::factory()->create();
    $ticket = Ticket::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'new',
    ]);

    $ticket->update(['status' => 'completed']);

    Queue::assertPushed(ProcessSmsAutomationTrigger::class, function ($job) {
        // Check that the job has a delay
        return $job->delay !== null;
    });
});

test('inactive automation triggers do not fire', function (): void {
    Queue::fake();

    $template = SmsTemplate::create([
        'name' => 'Inactive Template',
        'key' => 'inactive',
        'message' => 'This should not be sent.',
        'is_active' => true,
    ]);

    SmsAutomationTrigger::create([
        'name' => 'Inactive Trigger',
        'trigger_event' => 'ticket_status_changed',
        'sms_template_id' => $template->id,
        'delay_minutes' => 0,
        'send_to_customer' => true,
        'is_active' => false, // Inactive trigger
        'created_by' => $this->admin->id,
    ]);

    $customer = Customer::factory()->create();
    $ticket = Ticket::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'new',
    ]);

    $ticket->update(['status' => 'completed']);

    Queue::assertNotPushed(ProcessSmsAutomationTrigger::class);
});

test('automation trigger variables are correctly replaced', function (): void {
    $template = SmsTemplate::create([
        'name' => 'Variable Test Template',
        'key' => 'variable_test',
        'message' => 'Hello {customer_name}, your ticket {ticket_number} status is {status}.',
        'is_active' => true,
    ]);

    $customer = Customer::factory()->create([
        'phone' => '+233123456789',
    ]);

    $ticket = Ticket::factory()->create([
        'customer_id' => $customer->id,
        'ticket_number' => 'TKT-001',
        'status' => 'completed',
    ]);

    $trigger = SmsAutomationTrigger::create([
        'name' => 'Variable Test',
        'trigger_event' => 'ticket_status_changed',
        'sms_template_id' => $template->id,
        'delay_minutes' => 0,
        'send_to_customer' => true,
        'is_active' => true,
        'created_by' => $this->admin->id,
    ]);

    $variables = $trigger->generateVariables($ticket);
    $processedMessage = $template->render($variables);

    expect($processedMessage)->toContain('TKT-001');
    expect($processedMessage)->toContain('Completed');
    expect($processedMessage)->toContain($customer->name);
});
