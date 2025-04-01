<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Stripe;
use App\Models\Subscription; // Your Subscription model that extends Cashier's Subscription
use App\Models\UserPackage;
use Carbon\Carbon;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Set your Stripe secret key from configuration
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        // Retrieve the webhook secret from configuration (set in config/services.php)
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            Log::error('Stripe Webhook Error: ' . $e->getMessage());
            return response('Webhook Error', 400);
        }

        if ($event->type === 'customer.subscription.created') {
            $stripeSubscription = $event->data->object;
            Log::info('Subscription created: ' . $stripeSubscription->id);
        
            // Find the customer by stripe_id
            $customer = \App\Models\Customer::where('stripe_id', $stripeSubscription->customer)->first();
            if ($customer) {
                // Create local subscription record if missing, making sure to set the owner
                $subscription = $customer->subscriptions()->firstOrNew([
                    'stripe_id' => $stripeSubscription->id,
                ]);
                $subscription->fill([
                    'name'                   => 'default',
                    'stripe_status'          => $stripeSubscription->status,
                    'stripe_price'           => $stripeSubscription->items->data[0]->price->id,
                    'quantity'               => 1,
                    'current_period_start'   => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start),
                    'current_period_end'     => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
                ]);
                // This will set owner_id and owner_type using the customer's relationship
                $subscription->owner()->associate($customer);
                $subscription->save();
            }
        }
        
        

        // Handle subscription updated or created events
        if ($event->type === 'customer.subscription.updated' || $event->type === 'customer.subscription.created') {
            $stripeSubscription = $event->data->object;
            Log::info('Subscription event received: ' . $stripeSubscription->id);

            // Find the local subscription record by stripe_id
            $localSubscription = Subscription::where('stripe_id', $stripeSubscription->id)->first();

            if ($localSubscription) {
                // Convert UNIX timestamps to proper datetime strings using Carbon,
                // but if the value is too low (e.g. less than 1,000,000,000), use fallback logic.
                if (isset($stripeSubscription->current_period_start) && $stripeSubscription->current_period_start >= 1000000000) {
                    $currentPeriodStart = Carbon::createFromTimestamp($stripeSubscription->current_period_start)->toDateTimeString();
                } else {
                    // Fallback: use the local subscription's created_at as the period start
                    $currentPeriodStart = $localSubscription->created_at->toDateTimeString();
                    Log::warning('Invalid current_period_start from Stripe, using fallback based on created_at', [
                        'fallback_start' => $currentPeriodStart,
                    ]);
                }

                if (isset($stripeSubscription->current_period_end) && $stripeSubscription->current_period_end >= 1000000000) {
                    $currentPeriodEnd = Carbon::createFromTimestamp($stripeSubscription->current_period_end)->toDateTimeString();
                } else {
                    // Fallback: assume a monthly subscription period; add one month to the start time
                    $fallbackStart = Carbon::parse($currentPeriodStart);
                    $currentPeriodEnd = $fallbackStart->copy()->addMonth()->toDateTimeString();
                    Log::warning('Invalid current_period_end from Stripe, using fallback (start + 1 month)', [
                        'fallback_end' => $currentPeriodEnd,
                    ]);
                }

                // Update local subscription record
                $localSubscription->current_period_start = $currentPeriodStart;
                $localSubscription->current_period_end   = $currentPeriodEnd;
                $localSubscription->stripe_status          = $stripeSubscription->status;
                $localSubscription->save();

                Log::info('Local subscription updated', [
                    'stripe_id'              => $stripeSubscription->id,
                    'current_period_start'   => $currentPeriodStart,
                    'current_period_end'     => $currentPeriodEnd,
                ]);
            } else {
                Log::warning('Local subscription not found for stripe_id: ' . $stripeSubscription->id);
            }
        }

        // Handle subscription deleted (canceled) event
        if ($event->type === 'customer.subscription.deleted') {
            $stripeSubscription = $event->data->object;
            Log::info('Subscription canceled: ' . $stripeSubscription->id);
        
            // Update the local subscriptions table using stripe_id
            $localSubscription = Subscription::where('stripe_id', $stripeSubscription->id)->first();
            if ($localSubscription) {
                $localSubscription->stripe_status = $stripeSubscription->status;
                $localSubscription->save();
                Log::info('Local subscription canceled updated', ['stripe_id' => $stripeSubscription->id]);
        
                // Update the user_packages table using stripe_id as well
                $userPackage = UserPackage::where('stripe_id', $localSubscription->stripe_id)->first();
                if ($userPackage) {
                    $userPackage->status = 0; // 0 for inactive/expired
                    $userPackage->save();
                    Log::info('User package status updated to inactive', [
                        'stripe_id' => $localSubscription->stripe_id,
                    ]);
                }
            }
        }
        

        // Handle invoice finalized event
        if ($event->type === 'invoice.finalized') {
            $invoice = $event->data->object;
            Log::info('Invoice finalized: ' . $invoice->id . ' with URL: ' . ($invoice->hosted_invoice_url ?? 'none'));

            // The invoice object has a subscription field referencing the Stripe subscription ID
            if (!empty($invoice->subscription)) {
                // Find the local subscription
                $localSubscription = \App\Models\Subscription::where('stripe_id', $invoice->subscription)->first();
                if ($localSubscription) {
                    $localSubscription->latest_invoice_url = $invoice->hosted_invoice_url ?? null;
                    $localSubscription->save();

                    Log::info('Stored invoice URL in local subscription', [
                        'stripe_id' => $invoice->subscription,
                        'latest_invoice_url' => $invoice->hosted_invoice_url,
                    ]);
                }
            }
        }

        // Handle invoice payment failure
        if ($event->type === 'invoice.payment_failed') {
            $invoice = $event->data->object;
            Log::warning('Invoice payment failed: ' . $invoice->id);
        }

        return response('Webhook handled', 200);
    }
}
