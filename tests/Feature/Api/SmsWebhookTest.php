<?php

declare(strict_types=1);

use App\Models\SmsDeliveryLog;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\postJson;

beforeEach(function () {
    Config::set('services.texttango.webhook_secret', 'test_secret_key');
});

test('webhook updates sms delivery status successfully', function () {
    $log = SmsDeliveryLog::create([
        'phone' => '+1234567890',
        'message' => 'Test message',
        'status' => 'pending',
        'external_id' => 'msg_12345',
    ]);

    $payload = [
        'message_id' => 'msg_12345',
        'status' => 'delivered',
        'phone' => '+1234567890',
    ];

    $signature = hash_hmac('sha256', json_encode($payload), 'test_secret_key');

    $response = postJson('/webhooks/sms/delivery-status', $payload, [
        'X-TextTango-Signature' => $signature,
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Delivery status updated',
        ]);

    expect($log->fresh()->status)->toBe('sent');
});

test('webhook rejects invalid signature', function () {
    $log = SmsDeliveryLog::create([
        'phone' => '+1234567890',
        'message' => 'Test message',
        'status' => 'pending',
        'external_id' => 'msg_12345',
    ]);

    $payload = [
        'message_id' => 'msg_12345',
        'status' => 'delivered',
    ];

    $response = postJson('/webhooks/sms/delivery-status', $payload, [
        'X-TextTango-Signature' => 'invalid_signature',
    ]);

    $response->assertUnauthorized()
        ->assertJson([
            'success' => false,
            'message' => 'Invalid signature',
        ]);

    expect($log->fresh()->status)->toBe('pending');
});

test('webhook requires signature when secret is configured', function () {
    $log = SmsDeliveryLog::create([
        'phone' => '+1234567890',
        'message' => 'Test message',
        'status' => 'pending',
        'external_id' => 'msg_12345',
    ]);

    $payload = [
        'message_id' => 'msg_12345',
        'status' => 'delivered',
    ];

    $response = postJson('/webhooks/sms/delivery-status', $payload);

    $response->assertUnauthorized();

    expect($log->fresh()->status)->toBe('pending');
});

test('webhook works without signature when secret not configured', function () {
    Config::set('services.texttango.webhook_secret', null);

    $log = SmsDeliveryLog::create([
        'phone' => '+1234567890',
        'message' => 'Test message',
        'status' => 'pending',
        'external_id' => 'msg_12345',
    ]);

    $payload = [
        'message_id' => 'msg_12345',
        'status' => 'delivered',
    ];

    $response = postJson('/webhooks/sms/delivery-status', $payload);

    $response->assertSuccessful();

    expect($log->fresh()->status)->toBe('sent');
});

test('webhook handles failed status', function () {
    $log = SmsDeliveryLog::create([
        'phone' => '+1234567890',
        'message' => 'Test message',
        'status' => 'pending',
        'external_id' => 'msg_12345',
    ]);

    $payload = [
        'message_id' => 'msg_12345',
        'status' => 'failed',
        'error_message' => 'Invalid phone number',
    ];

    $signature = hash_hmac('sha256', json_encode($payload), 'test_secret_key');

    $response = postJson('/webhooks/sms/delivery-status', $payload, [
        'X-TextTango-Signature' => $signature,
    ]);

    $response->assertSuccessful();

    $log->refresh();

    expect($log->status)->toBe('failed')
        ->and($log->error_message)->toBe('Invalid phone number');
});

test('webhook returns 404 for non-existent message', function () {
    $payload = [
        'message_id' => 'non_existent_id',
        'status' => 'delivered',
    ];

    $signature = hash_hmac('sha256', json_encode($payload), 'test_secret_key');

    $response = postJson('/webhooks/sms/delivery-status', $payload, [
        'X-TextTango-Signature' => $signature,
    ]);

    $response->assertNotFound()
        ->assertJson([
            'success' => false,
            'message' => 'SMS delivery log not found',
        ]);
});

test('webhook validates required fields', function () {
    $payload = [
        'status' => 'delivered',
        // missing message_id
    ];

    $signature = hash_hmac('sha256', json_encode($payload), 'test_secret_key');

    $response = postJson('/webhooks/sms/delivery-status', $payload, [
        'X-TextTango-Signature' => $signature,
    ]);

    $response->assertUnprocessable();
});
