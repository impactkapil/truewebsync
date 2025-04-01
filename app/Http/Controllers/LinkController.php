<?php

namespace App\Http\Controllers;

use App\Models\LinkedProduct;
use App\Models\SelectedProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\ShopifyStore;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Models\UserPackage;
class LinkController extends Controller
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
    // public function index()
    // {
    //     $customer = Auth::guard('customer')->user();

    //     // Fetch products from `selected_products` where the related `shopify_stores` has `is_master = 0`
    //     $products = SelectedProduct::where('customer_id', $customer->id)
    //         ->whereHas('shopifyStore', function ($query) {
    //             $query->where('is_master', 0);
    //         })
    //         ->with('shopifyStore') // Eager load the related shopify store
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     return view('customer.inventory.newlinked', compact('products'));
    // }

    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        // 1) Fetch all secondary shops for the dropdown (is_master = 0)
        $secondaryShops = ShopifyStore::where('customer_id', $customer->id)
            ->where('is_master', 0)
            ->get();

        // 2) Check if a specific shop was selected
        $selectedShopId = $request->get('shop_id');

        // -------------------------------------------------
        // LINKED PRODUCTS
        // -------------------------------------------------
        $linkedQuery = SelectedProduct::where('customer_id', $customer->id)
            ->whereHas('linkedProducts') // must have at least one linked product
            ->with(['shopifyStore', 'linkedProducts'])
            ->orderBy('created_at', 'desc');

        if ($selectedShopId) {
            $linkedQuery->where('shopify_store_id', $selectedShopId);
        }

        // Optional search for linked products
        $linkedSearch = $request->input('linked_search');
        if ($linkedSearch) {
            $linkedQuery->where(function($q) use ($linkedSearch) {
                $q->where('product_title', 'like', "%{$linkedSearch}%")
                  ->orWhere('variant_name', 'like', "%{$linkedSearch}%")
                  ->orWhere('variant_sku', 'like', "%{$linkedSearch}%");
            });
        }

        $linkedPerPage = $request->input('linked_per_page', 10);
        $linkedProducts = $linkedQuery
            ->paginate($linkedPerPage, ['*'], 'linked_page')
            ->appends($request->query());

        // -------------------------------------------------
        // UNLINKED PRODUCTS
        // -------------------------------------------------
        $unlinkedQuery = SelectedProduct::where('customer_id', $customer->id)
            ->whereHas('shopifyStore', function ($q) {
                // Must belong to a secondary shop (is_master = 0)
                $q->where('is_master', 0);
            })
            ->doesntHave('linkedProducts') // must have no linked products
            ->with('shopifyStore')
            ->orderBy('created_at', 'desc');

        if ($selectedShopId) {
            $unlinkedQuery->where('shopify_store_id', $selectedShopId);
        }

        // Optional search for unlinked products
        $unlinkedSearch = $request->input('unlinked_search');
        if ($unlinkedSearch) {
            $unlinkedQuery->where(function($q) use ($unlinkedSearch) {
                $q->where('product_title', 'like', "%{$unlinkedSearch}%")
                  ->orWhere('variant_name', 'like', "%{$unlinkedSearch}%")
                  ->orWhere('variant_sku', 'like', "%{$unlinkedSearch}%");
            });
        }

        $unlinkedPerPage = $request->input('unlinked_per_page', 10);
        $unlinkedProducts = $unlinkedQuery
            ->paginate($unlinkedPerPage, ['*'], 'unlinked_page')
            ->appends($request->query());

        return view('customer.inventory.newlinked', [
            'secondaryShops'   => $secondaryShops,
            'linkedProducts'   => $linkedProducts,
            'unlinkedProducts' => $unlinkedProducts,
            'selectedShopId'   => $selectedShopId,
        ]);
    }

    /**
     * Search for unlinked products from the master shop.
     */
    public function searchUnlinkedProducts(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $query    = $request->input('query');

        $unlinkedMasterProducts = SelectedProduct::query()
            ->where('customer_id', $customer->id)
            ->whereHas('shopifyStore', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id)
                  ->where('is_master', 1);  // Only master shop products
            })
            ->doesntHave('linkedProducts')  // Exclude products already linked
            ->where(function ($q) use ($query) {
                $q->where('product_title', 'LIKE', "%{$query}%")
                  ->orWhere('variant_sku', 'LIKE', "%{$query}%");
            })
            ->get();

        return response()->json($unlinkedMasterProducts);
    }

    /**
     * Example: Search Master Shop Products (AJAX)
     * This is the route that your "Search Matching Product" field should call
     * to find products from the Master Shop (is_master=1) or whichever logic.
     */
    public function searchSelectedProducts(Request $request)
    {
        $query = $request->get('query', '');
        // Example: find products in Master Shop
        // Adjust logic to match your actual search requirements
        $results = SelectedProduct::whereHas('shopifyStore', function($q) {
                $q->where('is_master', 1);
            })
            ->where(function($q) use ($query) {
                $q->where('product_title', 'like', "%{$query}%")
                  ->orWhere('variant_name', 'like', "%{$query}%")
                  ->orWhere('variant_sku', 'like', "%{$query}%");
            })
            ->limit(20) // limit the results
            ->get(['id', 'product_title', 'variant_sku', 'variant_name']);

        return response()->json($results);
    }

    public function destroyUnlinkedProduct(Request $request)
    {
        // Retrieve the incoming 'product_ids' array.
        // If it's a single product, it's still an array with one element.
        $productIds = $request->input('product_ids');

        if (empty($productIds)) {
            return back()->with('error', 'No products selected for deletion.');
        }

        // Ensure $productIds is an array (in case you ever receive a single value)
        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }

        // Delete all matching IDs in the selected_products table
        SelectedProduct::whereIn('id', $productIds)->delete();

        return back()->with('success', 'Product(s) deleted successfully.');
    }

    public function linkProducts(Request $request)
    {
        // Validate incoming product IDs.
        $request->validate([
            'product_one_id' => 'required|exists:selected_products,id',
            'product_two_id' => 'required|exists:selected_products,id',
        ]);

        // IMPORTANT: For this feature:
        // - product_one_id is the secondary product (to be updated)
        // - product_two_id is the master product (inventory source)
        $secondaryProductId = $request->input('product_one_id');
        $masterProductId    = $request->input('product_two_id');

        // Check if this exact pair has already been linked.
        $alreadyLinked = LinkedProduct::query()
            ->where('product_one_id', $secondaryProductId)
            ->where('product_two_id', $masterProductId)
            ->exists();

        if ($alreadyLinked) {
            return response()->json([
                'success' => false,
                'message' => 'That specific pair of products has already been linked.',
            ], 422);
        }

        // Create the link record.
        LinkedProduct::create([
            'product_one_id' => $secondaryProductId,
            'product_two_id' => $masterProductId,
        ]);
        Log::info('LinkProducts: Created linked product record', [
            'secondary_product_id' => $secondaryProductId,
            'master_product_id'      => $masterProductId,
        ]);

        // Fetch products.
        $secondaryProduct = SelectedProduct::find($secondaryProductId);
        $masterProduct    = SelectedProduct::find($masterProductId);

        if (!$secondaryProduct || !$masterProduct) {
            return response()->json([
                'success' => false,
                'message' => 'One or both products not found.',
            ], 404);
        }

        // --- Compute the inventory value from the master product ---
        // Assume master product's variant_inventory is stored as JSON with one or more key/value pairs.
        $masterInvData = json_decode($masterProduct->variant_inventory, true);
        if (is_array($masterInvData) && count($masterInvData) > 0) {
            // Take the first value (e.g. 500) from the master product's inventory.
            $masterValue = reset($masterInvData);
        } else {
            $masterValue = 0;
        }

        // --- Get the secondary product's current location key ---
        // We assume its variant_inventory is stored as JSON; if not, try to use the store's default.
        $secondaryInvData = json_decode($secondaryProduct->variant_inventory, true);
        if (is_array($secondaryInvData) && count($secondaryInvData) > 0) {
            $secondaryLocationKey = array_key_first($secondaryInvData);
        } else {
            // Fallback: use the ShopifyStore's default location id if defined.
            $secondaryLocationKey = $secondaryProduct->shopifyStore->default_location_id ?? null;
        }
        if (!$secondaryLocationKey) {
            return response()->json([
                'success' => false,
                'message' => 'Secondary product has no location information available.',
            ], 422);
        }

        // --- Overwrite the secondary product's inventory locally ---
        $newSecondaryInv = [ $secondaryLocationKey => $masterValue ];
        $secondaryProduct->variant_inventory = json_encode($newSecondaryInv);
        $secondaryProduct->save();
        Log::info('LinkProducts: Updated secondary product local inventory', [
            'secondary_product_id' => $secondaryProduct->id,
            'new_inventory'        => $newSecondaryInv,
        ]);

        // --- Push the updated inventory to Shopify for the secondary product ---
        if ($secondaryProduct->shopifyStore && !empty($secondaryProduct->variant_id)) {
            $secondaryStore = $secondaryProduct->shopifyStore;
            // Use the secondary product's location key.
            try {
                $this->updateShopifyVariantInventory(
                    $secondaryStore,
                    $secondaryProduct->variant_id,
                    'set',
                    $masterValue, // new inventory value from master
                    0,            // currentLocalInv not needed for 'set'
                    $secondaryLocationKey
                );
                Log::info('LinkProducts: Updated Shopify inventory for secondary product', [
                    'secondary_product_id' => $secondaryProduct->id,
                    'variant_id'           => $secondaryProduct->variant_id,
                    'location_id'          => $secondaryLocationKey,
                    'quantity'             => $masterValue,
                ]);
            } catch (\Exception $e) {
                Log::error('LinkProducts: Error updating Shopify inventory for secondary product', [
                    'secondary_product_id' => $secondaryProduct->id,
                    'location_id'          => $secondaryLocationKey,
                    'quantity'             => $masterValue,
                    'error'                => $e->getMessage(),
                ]);
                // Optionally, return an error response.
            }
        } else {
            Log::info('LinkProducts: Secondary product missing ShopifyStore or variant_id', [
                'secondary_product_id' => $secondaryProduct->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Products linked successfully and secondary product inventory updated!',
        ]);
    }

    /**
     * Update Shopify variant inventory using GraphQL.
     *
     * This method fetches the inventoryItem ID for a variant using its variant_id and then
     * sends a GraphQL mutation to "set" or "adjust" the available inventory.
     *
     * @param  \App\Models\ShopifyStore $shopifyStore
     * @param  string $variantId
     * @param  string $operation ('set' or 'adjust')
     * @param  int    $qty            The new inventory quantity
     * @param  int    $currentLocalInv Not used when setting
     * @param  string $locationId     The location ID (local value)
     * @throws \Exception
     */
    private function updateShopifyVariantInventory($shopifyStore, $variantId, $operation, $qty, $currentLocalInv, $locationId)
    {
        Log::info("ENTERING updateShopifyVariantInventory", [
            'shopifyStoreDomain' => $shopifyStore->shopify_domain,
            'variantId'          => $variantId,
            'operation'          => $operation,
            'qty'                => $qty,
            'currentLocalInv'    => $currentLocalInv,
            'locationId_param'   => $locationId,
        ]);

        // Ensure the locationId is in the required GID format.
        if (!str_starts_with($locationId, 'gid://shopify/Location/')) {
            $locationId = "gid://shopify/Location/{$locationId}";
        }
        Log::info("Converted locationId to GID if needed", ['locationId' => $locationId]);

        $variantGid  = "gid://shopify/ProductVariant/{$variantId}";
        $shopDomain  = $shopifyStore->shopify_domain;
        $accessToken = $shopifyStore->access_token;
        $client = new Client();
        $url = "https://{$shopDomain}/admin/api/2023-10/graphql.json";

        // 1) Fetch the InventoryItem ID for the variant.
        $queryGetVariant = <<<'GRAPHQL'
query getVariantItem($variantId: ID!) {
  productVariant(id: $variantId) {
    id
    inventoryItem {
      id
      tracked
    }
  }
}
GRAPHQL;
        $varsGetVariant = ['variantId' => $variantGid];

        try {
            $resp1 = $client->post($url, [
                'headers' => [
                    'X-Shopify-Access-Token' => $accessToken,
                    'Content-Type'           => 'application/json'
                ],
                'json' => [
                    'query'     => $queryGetVariant,
                    'variables' => $varsGetVariant
                ]
            ]);
            $statusCode1 = $resp1->getStatusCode();
            Log::info("Response from getVariantItem request", ['status_code' => $statusCode1]);
            $data1 = json_decode($resp1->getBody()->getContents(), true);
            Log::info("Response from first GraphQL (productVariant)", ['data1' => $data1]);
        } catch (\Exception $e) {
            Log::error("Error during getVariantItem GraphQL call", ['error' => $e->getMessage()]);
            throw $e;
        }

        $inventoryItemId = $data1['data']['productVariant']['inventoryItem']['id'] ?? null;
        $isTracked       = $data1['data']['productVariant']['inventoryItem']['tracked'] ?? false;

        if (!$inventoryItemId) {
            throw new \Exception("No inventoryItemId found for variant=$variantId");
        }
        if (!$isTracked) {
            throw new \Exception("Variant=$variantId is not 'tracked'. Enable Shopify inventory tracking in admin.");
        }

        // 2) Build the GraphQL mutation to update the inventory.
        if ($operation === 'set') {
            $mutation = <<<'GRAPHQL'
mutation setOnHand($reason: String!, $setQuantities: [InventorySetQuantityInput!]!) {
  inventorySetOnHandQuantities(input: {
    reason: $reason
    setQuantities: $setQuantities
  }) {
    userErrors {
      field
      message
    }
  }
}
GRAPHQL;
            $vars2 = [
                'reason' => 'other',
                'setQuantities' => [[
                    'inventoryItemId' => $inventoryItemId,
                    'locationId'      => $locationId,
                    'quantity'        => $qty
                ]]
            ];
        } else {
            $mutation = <<<'GRAPHQL'
mutation adjustOnHand($input: InventoryAdjustQuantitiesInput!) {
  inventoryAdjustQuantities(input: $input) {
    userErrors {
      field
      message
    }
  }
}
GRAPHQL;
            $vars2 = [
                'input' => [
                    'name'   => 'available',
                    'reason' => 'other',
                    'changes' => [[
                        'inventoryItemId' => $inventoryItemId,
                        'locationId'      => $locationId,
                        'delta'           => $qty
                    ]]
                ]
            ];
        }

        Log::info("Sending second GraphQL to update inventory", [
            'operation' => $operation,
            'mutation'  => $mutation,
            'variables' => $vars2
        ]);

        try {
            $resp2 = $client->post($url, [
                'headers' => [
                    'X-Shopify-Access-Token' => $accessToken,
                    'Content-Type'           => 'application/json'
                ],
                'json' => [
                    'query'     => $mutation,
                    'variables' => $vars2
                ]
            ]);
            $statusCode2 = $resp2->getStatusCode();
            Log::info("Response from inventory update request", ['status_code' => $statusCode2]);
            $data2 = json_decode($resp2->getBody()->getContents(), true);
            Log::info("Response from second GraphQL (set/adjust)", ['data2' => $data2]);
        } catch (\Exception $e) {
            Log::error("Error during inventory update GraphQL call", ['error' => $e->getMessage()]);
            throw $e;
        }

        if (!empty($data2['errors'])) {
            Log::error("Top-level GraphQL errors", ['errors' => $data2['errors']]);
            throw new \Exception('Shopify top-level error: ' . json_encode($data2['errors']));
        }

        if ($operation === 'set') {
            $userErrors = $data2['data']['inventorySetOnHandQuantities']['userErrors'] ?? [];
        } else {
            $userErrors = $data2['data']['inventoryAdjustQuantities']['userErrors'] ?? [];
        }
        if (!empty($userErrors)) {
            Log::error("Shopify userErrors found", ['userErrors' => $userErrors]);
            throw new \Exception('Shopify Inventory Error: ' . json_encode($userErrors));
        }
        Log::info("DONE updating Shopify inventory for variant=$variantId, operation=$operation, qty=$qty");
    }

    




    public function store(Request $request)
    {
        $request->validate([
            'product_one_id' => 'required|exists:selected_products,id',
            'product_two_id' => 'required|exists:selected_products,id|different:product_one_id',
        ]);

        $productOneId = $request->input('product_one_id');
        $productTwoId = $request->input('product_two_id');

        // Check if link already exists in either direction
        $exists = LinkedProduct::where(function ($q) use ($productOneId, $productTwoId) {
                $q->where('product_one_id', $productOneId)
                  ->where('product_two_id', $productTwoId);
            })
            ->orWhere(function ($q) use ($productOneId, $productTwoId) {
                $q->where('product_one_id', $productTwoId)
                  ->where('product_two_id', $productOneId);
            })
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'These products are already linked!');
        }

        LinkedProduct::create([
            'product_one_id' => $productOneId,
            'product_two_id' => $productTwoId,
        ]);

        return redirect()->back()->with('success', 'Products linked successfully!');
    }

    public function fetchLinkedProducts()
    {
        $customer = Auth::guard('customer')->user();

        // Fetch products that are linked
        $linkedProducts = SelectedProduct::where('customer_id', $customer->id)
            ->whereHas('linkedProducts')
            ->with(['shopifyStore', 'linkedProducts'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customer.inventory.linked', compact('linkedProducts'));
    }

    public function unlinkProducts(Request $request)
    {
        $productOneId = $request->input('product_one_id');

        // Delete the link from the pivot table
        DB::table('linked_products')
            ->where('product_one_id', $productOneId)
            ->orWhere('product_two_id', $productOneId)
            ->delete();

        // Redirect to a GET route that shows the linked products (or any valid page)
        return redirect()
            ->route('customer.linkProducts.list') // <-- or some other GET route
            ->with('success', 'Product link removed successfully.');
    }


}

