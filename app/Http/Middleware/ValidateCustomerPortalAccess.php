<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Customer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateCustomerPortalAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $customerId = $request->route('customer');
        $token = $request->route('token');

        if (! $customerId || ! $token) {
            return redirect()->route('portal.login')->with('error', 'Invalid portal access.');
        }

        $customer = Customer::validatePortalToken($token, $customerId);

        if (! $customer) {
            return redirect()->route('portal.login')->with('error', 'Invalid or expired portal access token.');
        }

        // Store customer in request for access in components
        $request->merge(['portal_customer' => $customer]);

        return $next($request);
    }
}
