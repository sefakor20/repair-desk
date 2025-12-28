<?php

declare(strict_types=1);

use App\Services\SmsService;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    config(['services.texttango.api_key' => 'test-api-key']);
    config(['services.texttango.sender_id' => 'TestSender']);
    config(['services.texttango.url' => 'https://app.texttango.com/api/v1/sms/campaign/send']);
});

test('sms service is enabled when api key is configured', function (): void {
    $service = new SmsService();

    expect($service->isEnabled())->toBeTrue();
});

test('sms service is disabled when api key is not configured', function (): void {
    config(['services.texttango.api_key' => '']);

    $service = new SmsService();

    expect($service->isEnabled())->toBeFalse();
});

test('send method sends sms to single recipient', function (): void {
    Http::fake([
        '*' => Http::response(['status' => 'success'], 200),
    ]);

    $service = new SmsService();
    $result = $service->send('+1234567890', 'Test message');

    expect($result)->toBeTrue();

    Http::assertSent(function ($request): bool {
        return $request->url() === config('services.texttango.url')
            && $request->method() === 'POST';
    });
});

test('send bulk sends sms to multiple recipients', function (): void {
    Http::fake([
        '*' => Http::response(['status' => 'success'], 200),
    ]);

    $service = new SmsService();
    $result = $service->sendBulk(['+1234567890', '+0987654321'], 'Bulk message');

    expect($result)->toBeTrue();

    Http::assertSent(function ($request): bool {
        return $request->url() === config('services.texttango.url');
    });
});

test('send formats phone numbers correctly', function (): void {
    Http::fake([
        '*' => Http::response(['status' => 'success'], 200),
    ]);

    $service = new SmsService();
    $result = $service->send('+1 (234) 567-8900', 'Test message');

    expect($result)->toBeTrue();

    Http::assertSent(function ($request): bool {
        return $request->url() === config('services.texttango.url');
    });
});

test('send returns false when http request fails', function (): void {
    Http::fake([
        '*' => Http::response(['error' => 'Failed'], 500),
    ]);

    $service = new SmsService();
    $result = $service->send('+1234567890', 'Test message');

    expect($result)->toBeFalse();
});

test('send returns false when api key is not configured', function (): void {
    config(['services.texttango.api_key' => '']);

    $service = new SmsService();
    $result = $service->send('+1234567890', 'Test message');

    expect($result)->toBeFalse();
});

test('send returns false when recipients array is empty', function (): void {
    $service = new SmsService();
    $result = $service->sendBulk([], 'Test message');

    expect($result)->toBeFalse();
});
