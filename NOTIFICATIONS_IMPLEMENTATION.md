# Enhanced Notifications System - Implementation Summary

## Overview

This document summarizes the implementation of the enhanced notifications system with SMS support using TextTango API integration. The system provides comprehensive notifications for ticket status changes, repair completions, and low stock alerts via both email and SMS channels.

## Features Implemented

### 1. SMS Integration with TextTango API

-   **Service**: `app/Services/SmsService.php`
-   **API Endpoint**: `https://app.texttango.com/api/v1/sms/campaign/send`
-   **Features**:
    -   Send SMS to single recipient
    -   Send bulk SMS to multiple recipients
    -   Phone number formatting (removes spaces, dashes)
    -   Configuration checking (isEnabled method)
    -   Error logging for failed requests

### 2. Custom SMS Notification Channel

-   **Channel**: `app/Channels/SmsChannel.php`
-   **Purpose**: Enables Laravel notifications to send SMS messages
-   **Integration**: Works seamlessly with Laravel's notification system

### 3. Notification Classes

#### TicketStatusChanged

-   **File**: `app/Notifications/TicketStatusChanged.php`
-   **Trigger**: Automatically sent when a ticket's status changes
-   **Channels**: Email + SMS (if customer has phone)
-   **Content**:
    -   Ticket number
    -   Old and new status
    -   Device name
    -   Customer portal link for tracking

#### RepairCompleted

-   **File**: `app/Notifications/RepairCompleted.php`
-   **Trigger**: Automatically sent when a ticket's status changes to "Completed"
-   **Channels**: Email + SMS (if customer has phone)
-   **Content**:
    -   Ticket number
    -   Device details
    -   Completion date
    -   Invoice balance (if invoice exists)
    -   Pickup instructions

#### LowStockAlert

-   **File**: `app/Notifications/LowStockAlert.php`
-   **Trigger**: Manually via console command (can be scheduled)
-   **Recipients**: Admins and Managers only
-   **Channels**: Email + SMS (if admin/manager has phone)
-   **Content**:
    -   List of low stock items
    -   Current quantity and reorder level for each item
    -   Direct link to inventory management

### 4. Automatic Notification Observer

-   **File**: `app/Observers/TicketObserver.php`
-   **Purpose**: Automatically detects ticket status changes and sends appropriate notifications
-   **Logic**:
    -   Detects when ticket status changes
    -   Sends TicketStatusChanged notification with old and new status
    -   If new status is "Completed", also sends RepairCompleted notification
    -   Works seamlessly with enum conversion

### 5. Console Command for Low Stock Alerts

-   **Command**: `php artisan inventory:check-low-stock`
-   **File**: `app/Console/Commands/CheckLowStockCommand.php`
-   **Purpose**: Check inventory for items at or below reorder level
-   **Recipients**: Active Admins and Managers
-   **Usage**: Can be added to Laravel scheduler for automated checks

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
TEXTTANGO_API_KEY=your_texttango_api_key_here
TEXTTANGO_SENDER_ID=RepairDesk
TEXTTANGO_API_URL=https://app.texttango.com/api/v1/sms/campaign/send
```

### Services Configuration

Configuration is stored in `config/services.php`:

```php
'texttango' => [
    'api_key' => env('TEXTTANGO_API_KEY'),
    'sender_id' => env('TEXTTANGO_SENDER_ID', 'RepairDesk'),
    'url' => env('TEXTTANGO_API_URL', 'https://app.texttango.com/api/v1/sms/campaign/send'),
],
```

## Database Changes

### Migration: Make Customer Phone Field Nullable

-   **File**: `database/migrations/2025_11_27_163323_make_customers_phone_nullable.php`
-   **Change**: Made `customers.phone` field nullable
-   **Reason**: SMS notifications are optional; customers without phone numbers can still use the system

## Testing

### Test Coverage

-   **Total Tests**: 1168 passing (6 skipped)
-   **Notification Tests**: 15 comprehensive tests covering:
    -   SMS and email content generation
    -   Channel selection based on phone availability
    -   Notification delivery
    -   Observer behavior

### Test Files Created

1. `tests/Unit/Services/SmsServiceTest.php` - SMS service unit tests
2. `tests/Feature/Notifications/TicketStatusChangedTest.php` - Ticket status notification tests
3. `tests/Feature/Notifications/RepairCompletedTest.php` - Repair completion notification tests
4. `tests/Feature/Notifications/LowStockAlertTest.php` - Low stock alert tests

### Running Tests

```bash
# Run all tests
php artisan test

# Run notification tests only
php artisan test --filter=Notification

# Run specific test file
php artisan test tests/Feature/Notifications/TicketStatusChangedTest.php
```

## Usage Examples

### Sending Notifications Manually

```php
use App\Notifications\TicketStatusChanged;
use App\Models\Customer;
use App\Enums\TicketStatus;

// Send ticket status change notification
$customer = Customer::find($customerId);
$customer->notify(new TicketStatusChanged(
    $ticket,
    TicketStatus::New->value,
    TicketStatus::InProgress->value
));

// Send repair completed notification
use App\Notifications\RepairCompleted;
$customer->notify(new RepairCompleted($ticket));

// Send low stock alert to admins
use App\Notifications\LowStockAlert;
use App\Models\User;
use App\Enums\Role;

$admins = User::whereIn('role', [Role::Admin, Role::Manager])
    ->where('is_active', true)
    ->get();

$lowStockItems = InventoryItem::whereRaw('quantity <= reorder_level')
    ->where('status', 'active')
    ->get();

Notification::send($admins, new LowStockAlert($lowStockItems));
```

### Scheduling Low Stock Alerts

Add to `routes/console.php` or `app/Console/Kernel.php`:

```php
use Illuminate\Support\Facades\Schedule;

// Check for low stock daily at 9 AM
Schedule::command('inventory:check-low-stock')->dailyAt('09:00');

// Or check multiple times per day
Schedule::command('inventory:check-low-stock')->twiceDaily(9, 17);
```

## How It Works

### Automatic Ticket Status Notifications

1. **Ticket Updated**: When a ticket's status is changed (e.g., via Livewire form)
2. **Observer Triggered**: `TicketObserver::updated()` method is called automatically
3. **Status Check**: Observer checks if status field was changed using `wasChanged('status')`
4. **Get Old Status**: Retrieves original status value using `getOriginal('status')`
5. **Send Notification**: Sends `TicketStatusChanged` notification to customer
6. **Completion Check**: If new status is "Completed", also sends `RepairCompleted` notification
7. **Queue Processing**: Notifications are queued for background processing (implements `ShouldQueue`)

### SMS Channel Selection Logic

Notifications automatically determine whether to include SMS based on:

```php
public function via($notifiable): array
{
    $channels = ['mail'];

    if ($notifiable->phone) {
        $channels[] = SmsChannel::class;
    }

    return $channels;
}
```

-   If customer/user has a phone number: **Email + SMS**
-   If customer/user has no phone number: **Email only**

### SMS Content Format

SMS messages are concise and include essential information:

**Ticket Status Changed:**

```
Your repair ticket #TKT-001 status has been updated from New to In Progress. Device: iPhone 13 Pro.
```

**Repair Completed:**

```
Good news! Your repair for iPhone 13 Pro (TKT-001) is complete! Balance due: GHS 150.00. Please visit to pick up your device.
```

**Low Stock Alert:**

```
Low Stock Alert: iPhone 13 Screen (3 left), Samsung S21 Battery (2 left), USB-C Cable (1 left)
```

## Portal Access Email

The customer portal access link email is properly formatted and includes:

-   Customer's name
-   Secure access link with token
-   Instructions for accessing the portal
-   Link expiration information

**Template**: `resources/views/emails/portal/access-link.blade.php`

## Key Features & Benefits

✅ **Automatic Notifications**: No manual intervention needed; tickets automatically trigger notifications
✅ **Multi-Channel**: Email + SMS for maximum reach
✅ **Intelligent Channel Selection**: SMS only sent if recipient has phone number
✅ **Queue Support**: All notifications queued for background processing
✅ **Comprehensive Testing**: 15 dedicated tests ensure reliability
✅ **Error Handling**: Graceful failure handling with logging
✅ **Configurable**: Easy to enable/disable SMS via environment variables
✅ **Scalable**: Supports bulk SMS sending for multiple recipients

## Technical Details

### SMS Service Architecture

```php
class SmsService
{
    - Validates API configuration
    - Formats phone numbers consistently
    - Uses Laravel HTTP client for reliable API communication
    - Logs all requests and errors
    - Returns boolean success/failure
    - Supports single and bulk sending
}
```

### Observer Pattern Benefits

-   **Automatic**: No need to remember to send notifications manually
-   **Consistent**: All ticket status changes trigger notifications
-   **Maintainable**: Centralized notification logic
-   **Testable**: Easy to mock and test

### Database Optimization

-   Customer phone field made nullable for flexibility
-   Observer uses `wasChanged()` for efficient change detection
-   Notifications queued to prevent blocking ticket updates

## Troubleshooting

### SMS Not Sending

1. Check TextTango API key is configured in `.env`
2. Verify API key is valid and active
3. Check `storage/logs/laravel.log` for error messages
4. Ensure customer/user has valid phone number
5. Test SMS service directly:

```php
use App\Services\SmsService;

$smsService = app(SmsService::class);
$result = $smsService->send('+233123456789', 'Test message');

if ($result) {
    echo "SMS sent successfully!";
} else {
    echo "SMS failed. Check logs for details.";
}
```

### Notifications Not Triggering

1. Verify observer is registered in `AppServiceProvider::boot()`
2. Check that `Customer` model uses `Notifiable` trait
3. Ensure ticket status actually changed (not set to same value)
4. Verify queue worker is running: `php artisan queue:work`

### Testing Issues

If tests fail:

```bash
# Run migrations
php artisan migrate --env=testing

# Clear caches
php artisan config:clear
php artisan cache:clear

# Run specific test suite
php artisan test --filter=Notification
```

## Future Enhancements

Potential improvements for future iterations:

1. **Notification Preferences**: Allow customers to opt-out of SMS notifications
2. **SMS Templates**: Create reusable SMS templates in database
3. **Delivery Tracking**: Track SMS delivery status via TextTango webhooks
4. **A/B Testing**: Test different notification wording for better engagement
5. **Scheduled Reminders**: Send reminder SMS for pending pickups
6. **WhatsApp Integration**: Add WhatsApp as additional notification channel
7. **Push Notifications**: Mobile app push notifications

## Support & Documentation

-   **TextTango API Docs**: Contact TextTango support for API documentation
-   **Laravel Notifications**: https://laravel.com/docs/12.x/notifications
-   **Queue Workers**: https://laravel.com/docs/12.x/queues

## Summary

The enhanced notifications system provides:

-   **Automatic** ticket status and completion notifications
-   **Multi-channel** delivery (Email + SMS)
-   **Intelligent** recipient management
-   **Robust** error handling and logging
-   **Comprehensive** test coverage
-   **Easy** configuration and deployment

All 1168 tests passing ✅
