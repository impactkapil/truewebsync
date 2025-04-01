<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Signifly\Shopify\Shopify;
use Illuminate\Support\Facades\Auth;
use App\Models\ShopifyStore; 
use Illuminate\Support\Facades\Log;
use App\Models\UserPackage;

class ShopifyStoreController extends Controller
{
    public function __construct()
    {
        
        $this->middleware(function ($request, $next) {
            $customer = Auth::guard('customer')->user();
            $activePackage = \App\Models\UserPackage::where('customer_id', $customer->id)
                ->where('status', 1)
                ->first();
    
            if (!$activePackage) {
                return redirect()->route('customer.dashboard')
                    ->with('error', 'You do not have any active package at the moment.');
            }
            return $next($request);
        });
    }
    
    public function auth(Shopify $shopify)
    {
        $customer = Auth::guard('customer')->user();

        $authUrl = $shopify->getAuthorizeUrl();

        return redirect($authUrl);
    }

    
    public function callback(Request $request, Shopify $shopify)
    {
        $shopifyAccessToken = $shopify->getAccessToken($request->code);

        $shopDomain = $shopify->getShop();

        try {
            $customer = Auth::guard('customer')->user();

            $existingStore = ShopifyStore::where('customer_id', $customer->id)
                                         ->where('shopify_domain', $shopDomain)
                                         ->first();

            if (!$existingStore) {
                ShopifyStore::create([
                    'customer_id' => $customer->id,
                    'shopify_domain' => $shopDomain,
                    'access_token' => encrypt($shopifyAccessToken), // Encrypt the access token
                    'status' => 1, // Store is active by default
                ]);
            } else {
                $existingStore->update([
                    'access_token' => encrypt($shopifyAccessToken),
                ]);
            }

            return redirect()->route('customer.stores')->with('success', 'Store added successfully.');
        } catch (\Exception $e) {
            Log::error('Shopify OAuth callback error: ' . $e->getMessage());
            return redirect()->route('customer.stores')->with('error', 'Failed to add Shopify store.');
        }
    }
}
