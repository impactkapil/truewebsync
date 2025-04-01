<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function redirectToBillingPortal(Request $request)
    {
        $customer = auth('customer')->user();

        if (!$customer->stripe_id) {
            // Handle the case where they aren't a Stripe customer yet
            return redirect()->back()->withErrors('No Stripe customer found.');
        }

        // Cashier v13 provides this helper method
        return $customer->redirectToBillingPortal(route('customer.dashboard'));
    }
}
