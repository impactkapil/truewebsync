<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ShopifyStore;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SelectedProduct;

class ShopifyOrderController extends Controller
{
    public function index(Request $request)
    {
        // 1. Identify the logged-in customer
        $customerId = Auth::guard('customer')->id();

        // 2. Find all Shopify stores owned by this customer
        $storeIds = ShopifyStore::where('customer_id', $customerId)->pluck('id');

        // 3. Get the set of variant_ids from `selected_products`
        //    that belong to those store(s).
        $selectedVariantIds = \DB::table('selected_products')
            ->whereIn('shopify_store_id', $storeIds)
            ->pluck('variant_id')
            ->unique();

        // 4. Build a query for Orders that have at least one line item
        //    matching those variant IDs, and eager-load the orderItems
        //    plus the selectedProduct relationship.
        $ordersQuery = Order::whereIn('shopify_store_id', $storeIds)
            ->whereHas('orderItems', function($query) use ($selectedVariantIds) {
                $query->whereIn('shopify_variant_id', $selectedVariantIds);
            })
            ->with([
                'orderItems' => function ($query) use ($selectedVariantIds) {
                    $query->whereIn('shopify_variant_id', $selectedVariantIds)
                          ->with('selectedProduct');
                }
            ]);

        // 5. Optional search by order_name
        $search = $request->input('search');
        if ($search) {
            $ordersQuery->where('order_name', 'like', "%{$search}%");
        }

        // 6. Paginate with a "per_page" parameter
        $perPage = $request->input('per_page', 10);

        // 7. Order by newest or whichever you prefer
        $orders = $ordersQuery->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // 8. Return the view
        return view('customer.shopify.orders.index', compact('orders'));
    }
}
