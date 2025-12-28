<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\PosSale;
use App\Services\PaystackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Exception;

class PaystackCallbackController extends Controller
{
    public function __invoke(Request $request, PosSale $sale, PaystackService $paystackService): RedirectResponse
    {
        $reference = $request->query('reference');

        if (! $reference) {
            return redirect()
                ->route('pos.show', $sale)
                ->with('error', 'Payment reference is missing');
        }

        try {
            // Verify the transaction with Paystack
            $response = $paystackService->verifyTransaction($reference);

            if ($response['status'] && $response['data']['status'] === 'success') {
                // Update sale with successful payment
                $sale->update([
                    'payment_status' => 'completed',
                    'payment_metadata' => $response['data'],
                ]);

                return redirect()
                    ->route('pos.show', $sale)
                    ->with('success', 'Payment completed successfully!');
            }
            // Payment was not successful
            $sale->update([
                'payment_status' => 'failed',
                'payment_metadata' => $response['data'] ?? null,
            ]);
            return redirect()
                ->route('pos.show', $sale)
                ->with('error', 'Payment verification failed: ' . $response['message']);
        } catch (Exception $e) {
            $sale->update([
                'payment_status' => 'failed',
            ]);

            return redirect()
                ->route('pos.show', $sale)
                ->with('error', 'Payment verification error: ' . $e->getMessage());
        }
    }
}
