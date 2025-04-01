<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CheckSubscriptionActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $customer = auth('customer')->user();
        $subscription = $customer->subscription('default');

        // If there is no subscription or if the subscription is not active/valid, redirect
        if (!$subscription || !$subscription->valid()) {
            return redirect()->route('customer.subscription.expired')
                ->withErrors('Your subscription has expired or is inactive. Please renew your subscription to continue.');
        }

        // Optionally, calculate remaining time for debugging or informational purposes
        if ($subscription->current_period_end) {
            $timeRemaining = Carbon::createFromTimestamp($subscription->current_period_end)
                ->diffForHumans(null, Carbon::DIFF_ABSOLUTE);
            // You can attach this to the request if you want to display it later
            $request->merge(['subscription_time_remaining' => $timeRemaining]);
        }

        return $next($request);
    }
}
