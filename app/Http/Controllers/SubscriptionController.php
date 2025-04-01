<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Customer;
use App\Models\UserPackage;
use App\Models\Subscription;
use Exception;
use Stripe\Stripe;
use Stripe\Price;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class SubscriptionController extends Controller
{
    /**
     * Show the checkout form for the selected package.
     *
     * @param  int  $packageId
     * @return \Illuminate\View\View
     */
    public function showCheckoutForm($packageId)
    {

        $package = Package::findOrFail($packageId);
        $customer = auth('customer')->user();
        


        // Ensure the customer is a Stripe customer
        if (!$customer->stripe_id) {
            $customer->createAsStripeCustomer();
        }

        // Create a SetupIntent for multiple payment methods
        // (Make sure you have enabled these methods in your Stripe Dashboard)
        $setupIntent = $customer->createSetupIntent([
            'payment_method_types' => [
                'card',
                'bancontact',
                'ideal',
                'sepa_debit',
                'sofort',
                // Add other methods if needed
            ],
        ]);

        return view('subscription.checkout', compact('package', 'setupIntent'));
    }

    /**
     * Process the subscription creation using Laravel Cashier.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    // public function subscribe(Request $request)
    // {
    //     $request->validate([
    //         'package_id'     => 'required|exists:packages,id',
    //         'payment_method' => 'required|string',
    //         'card_holder'    => 'required|string'
    //     ]);
    
    //     $customer = auth('customer')->user();
    //     $package  = Package::findOrFail($request->package_id);
    
    //     try {
    //         // Create Stripe customer if needed
    //         if (!$customer->stripe_id) {
    //             $customer->createAsStripeCustomer();
    //         }
            
    //         // Update the default payment method
    //         $customer->updateDefaultPaymentMethod($request->payment_method);
    
    //         // Create the subscription with automatic tax enabled and expand invoice details
    //         $subscription = $customer->newSubscription('default', $package->stripe_price_id)
    //             ->create($request->payment_method, [
    //                 'automatic_tax' => ['enabled' => true],
    //                 'tax_id_collection' => ['enabled' => true],
    //                 'expand' => ['latest_invoice', 'latest_invoice.total_tax_amounts'],
    //             ]);
    
    //         // Ensure the subscription is linked to the owner (if not automatically set)
    //         if (is_null($subscription->owner_id) || is_null($subscription->owner_type)) {
    //             $subscription->owner_id = $customer->id;
    //             $subscription->owner_type = get_class($customer);
    //             $subscription->save();
    //         }
            
    //         $subscription->refresh();
    
    //         // Log the subscription details (Cashier-related)
    //         Log::info("Cashier Subscription Created", ['subscription' => $subscription->toArray()]);
    
    //         // Get the latest invoice from the subscription
    //         $latestInvoice = $subscription->latest_invoice;
    //         if ($latestInvoice) {
    //             // Log detailed tax information and other invoice tax details
    //             Log::info("Cashier Invoice Tax Details", [
    //                 'invoice_id'          => $latestInvoice->id,
    //                 'automatic_tax'       => $latestInvoice->automatic_tax,  // Contains enabled status & calculated amounts if available
    //                 'tax'                 => $latestInvoice->tax,              // Legacy tax field, may be null if using automatic tax
    //                 'tax_percent'         => $latestInvoice->tax_percent,      // Percentage if provided
    //                 'total_tax_amounts'   => method_exists($latestInvoice, 'total_tax_amounts') ? $latestInvoice->total_tax_amounts : null,
    //                 'line_items_count'    => method_exists($latestInvoice->lines, 'count') ? $latestInvoice->lines->count() : null,
    //                 'line_items'          => method_exists($latestInvoice->lines, 'toArray') ? $latestInvoice->lines->toArray() : null,
    //             ]);
    //         } else {
    //             Log::info("Cashier Invoice Tax Details", ['message' => 'No latest invoice found']);
    //         }
    
    //         // Log the hosted invoice URL (if available)
    //         $invoiceUrl = $latestInvoice->hosted_invoice_url ?? null;
    //         Log::info("Cashier Invoice URL", ['invoiceUrl' => $invoiceUrl]);
    
    //         // Determine subscription activation based on usage
    //         $currentShops    = \App\Models\ShopifyStore::where('customer_id', $customer->id)->count();
    //         $currentProducts = \App\Models\SelectedProduct::where('customer_id', $customer->id)->count();
    //         $activeStatus = ($currentShops <= $package->number_of_shops && $currentProducts <= $package->number_of_products) ? 1 : 0;
            
    //         \App\Models\UserPackage::create([
    //             'customer_id' => $customer->id,
    //             'package_id'  => $package->id,
    //             'stripe_id'   => $subscription->stripe_id,
    //             'card_holder' => $request->card_holder,
    //             'card_number' => null,
    //             'expiry_date' => null,
    //             'cvv'         => null,
    //             'status'      => $activeStatus
    //         ]);
    //         Log::info("UserPackage record created successfully", ['status' => $activeStatus]);
    
    //         $successMessage = $activeStatus == 1
    //             ? "Subscription activated successfully."
    //             : "Subscription created successfully but not activated due to usage limits. Your active subscription remains unchanged.";
    
    //         // return view('subscription.success', compact('invoiceUrl', 'successMessage'));
    //         return redirect()->route('customer.subscription.packages')
    //                      ->with('success', 'Subscription starts successfully.');
    
    //     } catch (\Exception $e) {
    //         Log::error("Error during subscription creation", ['error' => $e->getMessage()]);
    //         return back()->withErrors(['error' => $e->getMessage()]);
    //     }
    // }

    public function subscribe(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);
    
        $customer = auth('customer')->user();
        $package  = Package::findOrFail($request->package_id);
    
        // Create Stripe customer if needed
        if (! $customer->stripe_id) {
            $customer->createAsStripeCustomer();
        }
    
        $checkoutSession = $customer->newSubscription('default', $package->stripe_price_id)
            ->checkout([
                'success_url' => route('customer.subscription.success'),
                'cancel_url'  => route('customer.subscription.packages'),
                'automatic_tax'     => ['enabled' => true],
                'tax_id_collection' => ['enabled' => true],
                'billing_address_collection' => 'required',
                'customer_update' => [
                    'name'    => 'auto',
                    'address' => 'auto',
                ],
            ]);
    
        // Force a redirect to the Stripe Checkout URL.
        return redirect()->away($checkoutSession->url);
    }
    

    

    
    
    

    
    

    

    public function switchUserPackage($id)
    {
        $customer = auth('customer')->user();
    
        // Find the specific UserPackage record by its stripe_id and belonging to the current customer.
        $userPackage = \App\Models\UserPackage::where('stripe_id', $id)
                            ->where('customer_id', $customer->id)
                            ->first();
    
        if (!$userPackage) {
            return redirect()->back()->withErrors(['error' => 'Package not found.']);
        }
    
        // Retrieve the new package details from the packages table.
        $newPackage = \App\Models\Package::find($userPackage->package_id);
        if (!$newPackage) {
            return redirect()->back()->withErrors(['error' => 'Associated package not found.']);
        }
    
        // Count the user's current usage.
        $currentShops    = \App\Models\ShopifyStore::where('customer_id', $customer->id)->count();
        $currentProducts = \App\Models\SelectedProduct::where('customer_id', $customer->id)->count();
    
        // Check if the current usage exceeds the allowed limits of the new package.
        if ($currentShops > $newPackage->number_of_shops || $currentProducts > $newPackage->number_of_products) {
            $errorMsg = "Cannot switch to this package. Your current usage exceeds the allowed limits: ";
            if ($currentShops > $newPackage->number_of_shops) {
                $errorMsg .= "Shops: $currentShops (Allowed: {$newPackage->number_of_shops}). ";
            }
            if ($currentProducts > $newPackage->number_of_products) {
                $errorMsg .= "Products: $currentProducts (Allowed: {$newPackage->number_of_products}).";
            }
            return redirect()->back()->withErrors(['error' => $errorMsg]);
        }
    
        // Set all packages for this customer to inactive (status = 0).
        \App\Models\UserPackage::where('customer_id', $customer->id)->update(['status' => 0]);
    
        // Activate the selected package by setting status to 1.
        $userPackage->status = 1;
        $userPackage->save();
    
        return redirect()->route('customer.subscription.packages')->with('success', 'Subscription switched successfully.');
    }
    

    

    

    /**
     * Display the subscription success page.
     *
     * @return \Illuminate\View\View
     */
    // public function success()
    // {
    //     return view('subscription.success');
    // }

    public function success()
    {
        // 1) Set Stripe API key
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
    
        $customer = auth('customer')->user();
        // Try to find local subscription named "default"
        $subscription = $customer->subscription('default');
    
        // 2) If no local subscription, fetch the subscription list from Stripe
        if (!$subscription) {
            // Expand the latest_invoice when listing subscriptions
            $stripeSubscriptions = \Stripe\Subscription::all([
                'customer' => $customer->stripe_id,
                'limit'    => 1,
                'expand'   => ['data.latest_invoice'], // <--- key step
            ]);
    
            if (empty($stripeSubscriptions->data)) {
                return redirect()->route('customer.subscription.packages')
                                 ->withErrors('Subscription is not active yet.');
            }
    
            // Take the first subscription from Stripe
            $stripeSubscription = $stripeSubscriptions->data[0];
    
            // 3) Create a local subscription record (Cashier default fields)
            //    This sets owner_id => $customer->id, owner_type => 'App\Models\Customer'
            $subscription = $customer->subscriptions()->create([
                'name'                 => 'default',
                'stripe_id'            => $stripeSubscription->id,
                'stripe_status'        => $stripeSubscription->status,
                'stripe_price'         => $stripeSubscription->items->data[0]->price->id,
                'quantity'             => 1,
                'current_period_start' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start),
                'current_period_end'   => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
            ]);
        } 
        else {
            // 4) If local subscription exists, expand latest_invoice via asStripeSubscription()
            //    so we can get hosted_invoice_url
            $stripeSubscription = $subscription->asStripeSubscription(['latest_invoice']);
        }
    
        // 5) Retrieve hosted_invoice_url from the expanded subscription
        //    It's null if the invoice isn't finalized yet
        $invoiceUrl = $stripeSubscription->latest_invoice->hosted_invoice_url ?? null;
    
        // 6) Update the local subscription record with the latest data
        $subscription->update([
            'stripe_status'        => $stripeSubscription->status,
            'current_period_start' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start),
            'current_period_end'   => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
            'latest_invoice_url'   => $invoiceUrl, // <--- store it locally
        ]);
    
        // 7) Create a UserPackage record if one doesn't exist yet
        if (! \App\Models\UserPackage::where('stripe_id', $subscription->stripe_id)->exists()) {
            $package = \App\Models\Package::where('stripe_price_id', $subscription->stripe_price)->first();
    
            // (Optional) Check usage limits
            $currentShops    = \App\Models\ShopifyStore::where('customer_id', $customer->id)->count();
            $currentProducts = \App\Models\SelectedProduct::where('customer_id', $customer->id)->count();
            $activeStatus = ($currentShops <= $package->number_of_shops && $currentProducts <= $package->number_of_products)
                            ? 1
                            : 0;
    
            \App\Models\UserPackage::create([
                'customer_id' => $customer->id,
                'package_id'  => $package->id,
                'stripe_id'   => $subscription->stripe_id,
                'card_holder' => $customer->name,
                'status'      => $activeStatus,
            ]);
        }
    
        // 8) Redirect to your packages page with a success message
        return redirect()->route('customer.subscription.packages')
                         ->with('success', 'Subscription activated successfully!');
    }
    

    



    /**
     * Show the customer's subscriptions for management.
     *
     * @return \Illuminate\View\View
     */
    public function manageSubscriptions(Request $request)
    {
        $customer = auth('customer')->user();
        // Ensure we have a collection from the customer's subscriptions.
        $subscriptions = collect($customer->subscriptions);

        // Set Stripe API key.
        Stripe::setApiKey(config('services.stripe.secret'));

        // Get the active user package (status = 1) for this customer.
        $activeUserPackage = UserPackage::where('customer_id', $customer->id)
            ->where('status', 1)
            ->first();

        // Enrich each subscription with local package info.
        $subscriptions = $subscriptions->map(function ($subscription) {
            if ($subscription->stripe_price) {
                $localPackage = Package::where('stripe_price_id', $subscription->stripe_price)->first();
                if ($localPackage) {
                    $subscription->package_name = $localPackage->package_name;
                    $subscription->local_price  = $localPackage->price; // e.g., 99.99
                }
                // (Optional) Retrieve Stripe's Price details.
                try {
                    $priceObj = \Stripe\Price::retrieve($subscription->stripe_price);
                    $subscription->price_amount   = $priceObj->unit_amount;
                    $subscription->price_currency = $priceObj->currency;
                } catch (\Exception $e) {
                    $subscription->price_amount   = null;
                    $subscription->price_currency = null;
                }
            }
            return $subscription;
        });

        // Get search and per_page query parameters.
        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 10);

        // Filter subscriptions based on search term.
        if ($search) {
            $subscriptions = $subscriptions->filter(function ($subscription) use ($search) {
                $searchTerm = strtolower($search);
                $packageName = strtolower($subscription->package_name ?? '');
                $stripeStatus = strtolower($subscription->stripe_status ?? '');
                // Extend with additional fields as needed.
                return (strpos($packageName, $searchTerm) !== false) ||
                       (strpos($stripeStatus, $searchTerm) !== false);
            });
        }

        // Sort subscriptions by creation timestamp (if available) descending.
        $subscriptions = $subscriptions->sortByDesc(function ($subscription) {
            // Stripe subscriptions usually have a 'created' property (a UNIX timestamp).
            return $subscription->created ?? 0;
        });

        // Manual pagination of the collection.
        $currentPage = $request->query('page', 1);
        $total = $subscriptions->count();
        $currentPageItems = $subscriptions->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedSubscriptions = new LengthAwarePaginator(
            $currentPageItems,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('subscription.manage', [
            'subscriptions'   => $paginatedSubscriptions,
            'activeUserPackage' => $activeUserPackage,
            'search'          => $search,
            'perPage'         => $perPage,
        ]);
    }
    

// public function manageSubscriptions()
// {
//     // Retrieve all user package records for the authenticated customer,
//     // eager-loading the related package details.
//     $userPackages = \App\Models\UserPackage::where('customer_id', auth('customer')->id())
//                         ->with('package')
//                         ->paginate(10);

//     return view('subscription.manage', compact('userPackages'));
// }


    /**
     * Cancel the current active subscription.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancelSubscription($id)
    {
        $customer = auth('customer')->user();
        $subscription = $customer->subscriptions()->find($id);
    
        if ($subscription && $subscription->active()) {
            // Explicitly set the owner relation if it is missing
            $subscription->setRelation('owner', $customer);
    
            try {
                $subscription->cancel();
                return redirect()->route('customer.subscription.manage')
                                 ->with('success', 'Subscription canceled.');
            } catch (\Exception $e) {
                return redirect()->route('customer.subscription.manage')
                                 ->withErrors($e->getMessage());
            }
        }
    
        return redirect()->route('customer.subscription.manage')
                         ->withErrors('No active subscription found.');
    }
    


    /**
     * (Optional) Display subscription status details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function subscriptionStatus(Request $request)
    {
        $customer = auth('customer')->user();
        $subscription = $customer->subscription('default');

        $active = $subscription ? $subscription->active() : false;
        $valid  = $subscription ? $subscription->valid() : false;
        $timeRemaining = null;

        if ($subscription && $subscription->current_period_end) {
            $timeRemaining = Carbon::createFromTimestamp($subscription->current_period_end)
                ->diffForHumans(null, Carbon::DIFF_ABSOLUTE);
        }

        return view('subscription.status', compact('subscription', 'active', 'valid', 'timeRemaining'));
    }

    public function swapSubscription(Request $request)
{
    $request->validate([
        'package_id' => 'required|exists:packages,id',
    ]);

    $customer = auth('customer')->user();
    $newPackage = Package::findOrFail($request->package_id);
    $subscription = $customer->subscription('default');

    if (!$subscription || !$subscription->active()) {
        return redirect()->route('customer.subscription.manage')
            ->withErrors('No active subscription found to swap.');
    }

    try {
        // Debug: Retrieve the new Price object from Stripe
        $priceObj = Price::retrieve($newPackage->stripe_price_id);
        if (!$priceObj) {
            return redirect()->route('customer.subscription.manage')
                ->withErrors('The new package price could not be retrieved from Stripe.');
        }
        
        // Log tax rates (if any) for debugging
        // This might call a method like priceTaxRates() on the Price object
        // Check if $priceObj->tax_rates is available
        \Log::info('New Price Tax Rates: ', (array) $priceObj->tax_rates);

        // Swap the subscription to the new plan
        $subscription->swap($newPackage->stripe_price_id);

        return redirect()->route('customer.subscription.manage')
            ->with('success', 'Subscription plan updated successfully.');
    } catch (Exception $e) {
        return redirect()->route('customer.subscription.manage')
            ->withErrors(['error' => $e->getMessage()]);
    }
}

    public function showSwapOptions()
{
    $packages = Package::all(); // Or use a query to filter only paid packages, if needed.
    return view('subscription.swap', compact('packages'));
}

public function packages(Request $request)
    {
        $customer = auth('customer')->user();

        // Get the active user package (if any) for this customer.
        $activeUserPackage = UserPackage::where('customer_id', $customer->id)
            ->where('status', 1)
            ->first();

        // Retrieve the active subscription record if an active user package exists.
        // We match on stripe_id, because the subscriptions table does not have a package_id column.
        $activeSubscription = null;
        if ($activeUserPackage) {
            $activeSubscription = Subscription::where('customer_id', $customer->id)
                ->where('stripe_id', $activeUserPackage->stripe_id)
                ->first();
        }

        // Build the packages query.
        $packagesQuery = Package::query();

        // Get search and per_page query parameters.
        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 10);

        // Filter packages based on search term if provided.
        if ($search) {
            $packagesQuery->where('package_name', 'like', '%' . $search . '%');
        }

        // Order packages by creation time descending.
        $packages = $packagesQuery->orderBy('created_at', 'desc')->get();

        // Manual pagination of the packages collection.
        $currentPage = $request->query('page', 1);
        $total = $packages->count();
        $currentPageItems = $packages->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedPackages = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('subscription.manage2', [
            'packages'           => $paginatedPackages,
            'activeUserPackage'  => $activeUserPackage,
            'activeSubscription' => $activeSubscription,
            'search'             => $search,
            'perPage'            => $perPage,
        ]);
    }

    /**
     * Switch the customer's active subscription to a new plan.
     *
     * @param Request $request
     * @param int $subscriptionId   The ID of the active subscription record.
     * @param string $priceId       The new plan's Stripe price ID.
     */
  

     public function switchSubscription(Request $request, $subscriptionId, $priceId)
    {
        $customer     = auth('customer')->user();
        $subscription = $customer->subscriptions()->findOrFail($subscriptionId);
    
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
    
        // 1️⃣ Grab existing Stripe Subscription item ID
        $stripeSub = \Stripe\Subscription::retrieve(
            $subscription->stripe_id,
            ['expand' => ['items']]
        );
        $itemId = $stripeSub->items->data[0]->id;
    
        // 2️⃣ Preview upcoming invoice (simulate swap)
        $upcoming = \Stripe\Invoice::upcoming([
            'customer'           => $customer->stripe_id,
            'subscription'       => $subscription->stripe_id,
            'subscription_items' => [['id' => $itemId, 'price' => $priceId]],
        ]);
    
        // 3️⃣ Sum only prorated line‑items (ignore full next‑month charge)
        $amountDue = collect($upcoming->lines->data)
            ->filter(fn($line) => $line->proration)
            ->sum(fn($line) => $line->amount);
    
        if ($amountDue > 0) {
            // 4️⃣ Create Checkout Session for the prorated difference
            $session = \Stripe\Checkout\Session::create([
                'customer'   => $customer->stripe_id,
                'mode'       => 'payment',
                'line_items' => [[
                    'price_data' => [
                        'currency'     => $upcoming->currency,
                        'unit_amount'  => $amountDue,
                        'product_data'=> ['name' => 'Proration charge for plan switch'],
                    ],
                    'quantity' => 1,
                ]],
                'success_url' => route('customer.subscription.switchComplete', [
                    'subscription' => $subscriptionId,
                    'priceId'      => $priceId,
                ]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('customer.subscription.packages'),
            ]);
    
            return redirect($session->url);
        }
    
        // 5️⃣ No payment due → swap immediately (anchor unchanged)
        $updated = \Stripe\Subscription::update(
            $subscription->stripe_id,
            [
                'items'                => [['id' => $itemId, 'price' => $priceId]],
                'proration_behavior'   => 'create_prorations',
                'billing_cycle_anchor' => 'unchanged',
            ]
        );
    
        $subscription->update([
            'stripe_price'         => $priceId,
            'current_period_start' => Carbon::createFromTimestamp($updated->current_period_start),
            'current_period_end'   => Carbon::createFromTimestamp($updated->current_period_end),
        ]);
    
        UserPackage::where('customer_id', $customer->id)->update(['status' => 0]);
        $userPkg = UserPackage::where('stripe_id', $subscription->stripe_id)
                              ->where('customer_id', $customer->id)
                              ->first();
        if ($userPkg) {
            $userPkg->package_id = Package::where('stripe_price_id', $priceId)->value('id');
            $userPkg->status     = 1;
            $userPkg->save();
        }
    
        return redirect()->route('customer.subscription.packages')
                         ->with('success', 'Subscription switched successfully (no payment required).');
    }
     
     

     

     
   

    
    public function confirmSwitch(Request $request, $subscriptionId, $priceId)
    {
        $customer     = auth('customer')->user();
        $subscription = $customer->subscriptions()->findOrFail($subscriptionId);
    
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
    
        // Get existing Stripe subscription item ID
        $stripeSub = \Stripe\Subscription::retrieve(
            $subscription->stripe_id,
            ['expand' => ['items']]
        );
        $itemId = $stripeSub->items->data[0]->id;
    
        // Preview upcoming invoice for the switch
        $upcoming = \Stripe\Invoice::upcoming([
            'customer'           => $customer->stripe_id,
            'subscription'       => $subscription->stripe_id,
            'subscription_items' => [['id' => $itemId, 'price' => $priceId]],
        ]);
    
        // Filter to only prorated lines
        $prorationLines = collect($upcoming->lines->data)
            ->filter(fn($line) => $line->proration)
            ->map(fn($line) => [
                'description' => $line->description,
                'amount'      => number_format($line->amount / 100, 2),
            ]);
    
        $amountDue = collect($upcoming->lines->data)
            ->filter(fn($line) => $line->proration)
            ->sum(fn($line) => $line->amount) / 100;
            $package = Package::where('stripe_price_id', $priceId)->first();
        return view('subscription.confirm_switch', [
            'subscription'   => $subscription,
            'priceId'        => $priceId,
            'newPackageName' => Package::where('stripe_price_id', $priceId)->value('package_name'),
            'package'      => $package,
            'lines'          => $prorationLines,
            'amountDue'      => number_format($amountDue, 2),
            'currency'       => strtoupper($upcoming->currency),
        ]);
    }
    

    

public function completeSwitch(Request $request, $subscriptionId, $priceId)
{
    $sessionId = $request->query('session_id');
    if (! $sessionId) {
        return redirect()->route('customer.subscription.packages')
                         ->withErrors('Missing Stripe session ID.');
    }

    \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
    $session = \Stripe\Checkout\Session::retrieve($sessionId);

    if ($session->payment_status !== 'paid') {
        return redirect()->route('customer.subscription.packages')
                         ->withErrors('Payment was not completed.');
    }

    $customer     = auth('customer')->user();
    $subscription = $customer->subscriptions()->findOrFail($subscriptionId);

    // Get the existing Stripe subscription item ID
    $stripeSub = \Stripe\Subscription::retrieve(
        $subscription->stripe_id,
        ['expand' => ['items']]
    );
    $itemId = $stripeSub->items->data[0]->id;

    // Update the subscription price now that payment succeeded
    $updated = \Stripe\Subscription::update(
        $subscription->stripe_id,
        [
            'items'              => [['id' => $itemId, 'price' => $priceId]],
            'proration_behavior' => 'none',
        ]
    );

    // Sync local DB
    $subscription->update([
        'stripe_price'         => $priceId,
        'current_period_start' => Carbon::createFromTimestamp($updated->current_period_start),
        'current_period_end'   => Carbon::createFromTimestamp($updated->current_period_end),
    ]);

    UserPackage::where('customer_id', $customer->id)->update(['status' => 0]);
    $userPkg = UserPackage::where('stripe_id', $subscription->stripe_id)
                          ->where('customer_id', $customer->id)
                          ->first();

    if ($userPkg) {
        $userPkg->package_id = Package::where('stripe_price_id', $priceId)->value('id');
        $userPkg->status     = 1;
        $userPkg->save();
    }

    return redirect()->route('customer.subscription.packages')
                     ->with('success', 'Subscription switched and charged successfully!');
}



    
    /**
     * Display the subscribe form for a given package.
     *
     * This is a stub method. Implement your subscribe logic or view as needed.
     */
    public function subscribeForm(Request $request, $packageId)
    {
        // For now, we just return a simple message.
        return "Subscribe form for package ID: " . $packageId;
    }






}
