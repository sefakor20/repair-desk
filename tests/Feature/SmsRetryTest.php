<?php

declare(strict_types=1);

use App\Models\SmsDeliveryLog;
use App\Services\SmsService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

test('failed sms can be retried', function () {
    config([
        'services.texttango.api_key' => 'test-key',
        'services.texttango.url' => 'https://api.texttango.test/sms',
    ]);

    Http::fake([
        '*' => Http::response(['message_id' => 'test-123'], 200),
    ]);

    $log = SmsDeliveryLog::create([
        'phone' => '+1234567890',
        'message' => 'Test message',
        'notification_type' => 'test',
        'status' => 'failed',
        'error_message' => 'Initial failure',
        'retry_count' => 0,
        'max_retries' => 3,
        'segments' => 1,
    ]);

    $smsService = app(SmsService::class);
    $success = $smsService->retrySms($log);

    expect($success)->toBeTrue();
    expect($log->fresh()->retry_count)->toBe(1);
    expect($log->fresh()->last_retry_at)->not->toBeNull();
});

test('retry increments retry count', function () {
    config([
        'services.texttango.api_key' => 'test-key',
        'services.texttango.url' => 'https://api.texttango.test/sms',
    ]);

    Http::fake([
        '*' => Http::response(['error' => 'Failed'], 400),
    ]);

    $log = SmsDeliveryLog::create([
        'phone' => '+1234567890',
        'message' => 'Test message',
        'notification_type' => 'test',
        'status' => 'failed',
        'retry_count' => 0,
        'max_retries' => 3,
        'segments' => 1,
    ]);

    $smsService = app(SmsService::class);
    $smsService->retrySms($log);

    $log->refresh();
    expect($log->retry_count)->toBe(1);
    expect($log->last_retry_at)->not->toBeNull();
    expect($log->next_retry_at)->not->toBeNull();
});

test('retry respects max retries limit', function () {
    $log = SmsDeliveryLog::create([
        'phone' => '+1234567890',
        'message' => 'Test message',
        'notification_type' => 'test',
        'status' => 'failed',
        'retry_count' => 3,
        'max_retries' => 3,
        'segments' => 1,
    ]);

    expect($log->canRetry())->toBeFalse();

    $smsService = app(SmsService::class);
    $success = $smsService->retrySms($log);

    expect($success)->toBeFalse();
    expect($log->fresh()->retry_count)->toBe(3);
});

test('retry command finds and retries failed messages', function () {
    config([
        'services.texttango.api_key' => 'test-key',
        'services.texttango.url' => 'https://api.texttango.test/sms',
    ]);

    Http::fake([
        '*' => Http::response(['message_id' => 'test-123'], 200),
    ]);

    // Create failed messages ready for retry
    SmsDeliveryLog::create([
        'phone' => '+1234567890',
        'message' => 'Test 1',
        'notification_type' => 'test',
        'status' => 'failed',
        'retry_count' => 0,
        'max_retries' => 3,
        'segments' => 1,
    ]);

    SmsDeliveryLog::create([
        'phone' => '+0987654321',
        'message' => 'Test 2',
        'notification_type' => 'test',
        'status' => 'failed',
        'retry_count' => 1,
        'max_retries' => 3,
        'next_retry_at' => now()->subMinute(),
        'segments' => 1,
    ]);

    // Should not retry (max retries reached)
    SmsDeliveryLog::create([
        'phone' => '+1111111111',
        'message' => 'Test 3',
        'notification_type' => 'test',
        'status' => 'failed',
        'retry_count' => 3,
        'max_retries' => 3,
        'segments' => 1,
    ]);

    Artisan::call('sms:retry-failed');

    expect(SmsDeliveryLog::where('status', 'sent')->count())->toBe(2);
    expect(SmsDeliveryLog::where('status', 'failed')->count())->toBe(1);
});

test('exponential backoff increases delay between retries', function () {
    config([
        'services.texttango.api_key' => 'test-key',
        'services.texttango.url' => 'https://api.texttango.test/sms',
    ]);

    Http::fake([
        '*' => Http::response(['error' => 'Failed'], 400),
    ]);

    $log = SmsDeliveryLog::create([
        'phone' => '+1234567890',
        'message' => 'Test message',
        'notification_type' => 'test',
        'status' => 'failed',
        'retry_count' => 0,
        'max_retries' => 3,
        'segments' => 1,
    ]);

    $smsService = app(SmsService::class);

    // First retry: 2^0 = 1 minute delay
    $smsService->retrySms($log);
    $log->refresh();
    expect($log->retry_count)->toBe(1);
    expect($log->next_retry_at->diffInMinutes(now()))->toBeLessThanOrEqual(2);

    // Second retry: 2^1 = 2 minutes delay
    $log->update(['next_retry_at' => null]);
    $smsService->retrySms($log);
    $log->refresh();
    expect($log->retry_count)->toBe(2);
    expect($log->next_retry_at->diffInMinutes(now()))->toBeLessThanOrEqual(5);
});
