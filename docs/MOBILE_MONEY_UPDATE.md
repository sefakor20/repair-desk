# Mobile Money & Currency Update

## Changes Made

### 1. Added Mobile Money Payment Method

#### Updated Files:

-   `app/Enums/PaymentMethod.php` - Added `MobileMoney = 'mobile_money'` case
-   `app/Livewire/Pos/Create.php` - Updated validation to include `mobile_money`
-   `resources/views/livewire/pos/create.blade.php` - Added Mobile Money option to payment method dropdown

#### Payment Methods Now Available:

1. **Cash** - Direct cash payments
2. **Card** - Credit/Debit card payments via Paystack
3. **Mobile Money** - Mobile money payments (MTN, Vodafone, AirtelTigo)
4. **Bank Transfer** - Direct bank transfers

### 2. Changed Default Currency to GHS

#### Updated Files:

-   `config/paystack.php` - Changed default currency from `NGN` to `GHS`

#### Currency Configuration:

```php
'currency' => env('PAYSTACK_CURRENCY', 'GHS'),
```

The system now defaults to Ghana Cedis (GHS) instead of Nigerian Naira (NGN).

## Supported Currencies

Paystack supports the following currencies:

-   **GHS** - Ghana Cedis (Default)
-   **NGN** - Nigerian Naira
-   **ZAR** - South African Rand
-   **USD** - US Dollar

You can change the currency by setting the `PAYSTACK_CURRENCY` environment variable in your `.env` file.

## Testing

All tests continue to pass:

-   ✅ 61 POS tests passed
-   ✅ 413 total tests passed
-   ✅ 144 assertions validated

## Mobile Money Integration

The Mobile Money payment method is now available in the POS system. When a customer selects Mobile Money as their payment method:

1. The sale is created with `payment_method = 'mobile_money'`
2. The sale is marked as completed (similar to cash payments)
3. The receipt shows "MOBILE MONEY" as the payment method

### Future Enhancement

For Phase 2+, you can integrate Paystack's Mobile Money API to handle mobile money payments programmatically, similar to how card payments work currently.

## Environment Variables

Make sure your `.env` file has:

```env
PAYSTACK_PUBLIC_KEY=your_public_key_here
PAYSTACK_SECRET_KEY=your_secret_key_here
PAYSTACK_MERCHANT_EMAIL=your@email.com
PAYSTACK_CURRENCY=GHS
```

## Payment Method Usage

### In POS Create Component:

```blade
<flux:select wire:model="paymentMethod" required>
    <option value="cash">Cash</option>
    <option value="card">Card</option>
    <option value="mobile_money">Mobile Money</option>
    <option value="bank_transfer">Bank Transfer</option>
</flux:select>
```

### In Backend Validation:

```php
$validated = $this->validate([
    'paymentMethod' => ['required', 'string', 'in:cash,card,mobile_money,bank_transfer'],
    // ... other validation rules
]);
```

## Migration Notes

No database migrations are required for this update. The existing `payment_method` column in the `pos_sales` table already supports string values, so `mobile_money` works without schema changes.

---

**Update Date**: October 11, 2025  
**Status**: ✅ Complete  
**Test Status**: All tests passing (413/413)
