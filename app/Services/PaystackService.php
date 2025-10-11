<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaystackService
{
    protected ?string $secretKey;

    protected ?string $publicKey;

    protected ?string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('paystack.secretKey');
        $this->publicKey = config('paystack.publicKey');
        $this->baseUrl = config('paystack.paymentUrl');
    }

    /**
     * Initialize a payment transaction
     */
    public function initializeTransaction(array $data): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/transaction/initialize", $data);

        return $response->json();
    }

    /**
     * Verify a transaction
     */
    public function verifyTransaction(string $reference): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
        ])->get("{$this->baseUrl}/transaction/verify/{$reference}");

        return $response->json();
    }

    /**
     * Get transaction details
     */
    public function getTransaction(string $id): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
        ])->get("{$this->baseUrl}/transaction/{$id}");

        return $response->json();
    }

    /**
     * Get Paystack public key
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * Generate a unique transaction reference
     */
    public function generateReference(): string
    {
        return 'PS_' . time() . '_' . bin2hex(random_bytes(5));
    }
}
