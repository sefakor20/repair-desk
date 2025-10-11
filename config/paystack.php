<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Paystack Public Key
    |--------------------------------------------------------------------------
    |
    | Your Paystack public key from https://dashboard.paystack.com/#/settings/developer
    |
    */
    'publicKey' => env('PAYSTACK_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Paystack Secret Key
    |--------------------------------------------------------------------------
    |
    | Your Paystack secret key from https://dashboard.paystack.com/#/settings/developer
    |
    */
    'secretKey' => env('PAYSTACK_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Payment URL
    |--------------------------------------------------------------------------
    |
    | Paystack API endpoint
    |
    */
    'paymentUrl' => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),

    /*
    |--------------------------------------------------------------------------
    | Merchant Email
    |--------------------------------------------------------------------------
    |
    | The merchant's email address for receiving payment notifications
    |
    */
    'merchantEmail' => env('PAYSTACK_MERCHANT_EMAIL'),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | Currency for transactions (NGN, GHS, ZAR, USD)
    |
    */
    'currency' => env('PAYSTACK_CURRENCY', 'GHS'),
];
