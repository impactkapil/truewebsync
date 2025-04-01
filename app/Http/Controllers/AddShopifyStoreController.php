<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use App\Http\Controllers\Controller;
use App\Models\ShopifyStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\UserPackage;
use App\Models\SelectedProduct;
use App\Models\LinkedProduct;
use App\Models\ShopifyProduct;
use App\Jobs\ImportShopifyProductsJob;
use App\Jobs\ImportShopifyProductsJobV2;
use Illuminate\Pagination\LengthAwarePaginator;

class AddShopifyStoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:customer');
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

    /**
     * Show the form to add Shopify store.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('customer.shopify.add-store');
    }

    /**
     * Save the Shopify store details.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 1) Validate
        $validator = Validator::make($request->all(), [
            'store_name'           => 'required|string|max:255',
            'shopify_domain'       => 'required|string',
            'access_token'         => 'required|string',
            'webhooks_secret_key'  => 'nullable|string|max:255',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        // 2) Get current logged customer
        $customer = Auth::guard('customer')->user();
    
        try {
            // 3) Check user package
            $userPackage = UserPackage::where('customer_id', $customer->id)
                ->where('status', 1)
                ->with('package')
                ->first();
    
            if (!$userPackage || !$userPackage->package) {
                return redirect()->route('customer.shopify.stores')
                    ->with('error', 'No active package found. Please purchase a package first.');
            }
    
            $shopLimit = $userPackage->package->number_of_shops; // e.g., 5
    
            // 4) Count how many shops user already has
            $existingShopsCount = ShopifyStore::where('customer_id', $customer->id)->count();
    
            // 5) If user is at or above the limit, show error
            if ($existingShopsCount >= $shopLimit) {
                return redirect()->route('customer.shopify.stores')
                    ->with('error', 'You have reached the maximum number of shops allowed by your package.');
            }
    
            // 6) Determine if first store => is_master=1
            $isMaster = ($existingShopsCount === 0) ? 1 : 0;
    
            // 7) Create the new store record
            $newStore = ShopifyStore::create([
                'customer_id'          => $customer->id,
                'store_name'           => $request->store_name,
                'shopify_domain'       => $request->shopify_domain,
                'access_token'         => $request->access_token,
                'webhooks_secret_key'  => $request->webhooks_secret_key,
                'is_master'            => $isMaster,
                'total_products'       => 0,  // will be updated by the import job
                'imported_products'    => 0,
            ]);
    
            // 8) Dispatch the job to import products (runs asynchronously)
            ImportShopifyProductsJob::dispatch($newStore, $customer->id);
    
            // 9) Redirect to your store listing page
            return redirect()
                ->route('customer.shopify.stores')
                ->with('success', 'Your Shopify store has been added. Import is in progress.');
        } catch (\Exception $e) {
            Log::error('Error adding Shopify store: '.$e->getMessage());
            return redirect()->route('customer.shopify.stores')
                ->with('error', 'The store domain you are trying to add already exists or an error occurred.');
        }
    }
    
    public function editStore($id)
    {
        $store = ShopifyStore::find($id);

        if (!$store) {
            return redirect()->route('customer.shopify.stores')->with('error', 'Store not found.');
        }

        return view('customer.shopify.edit_shopify_store', compact('store'));
    }

    public function updateStore(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'store_name'          => 'required|string|max:255',
            'shopify_domain'      => 'required|string',
            'webhooks_secret_key' => 'nullable|string|max:255',
            'access_token'        => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $store = ShopifyStore::find($id);
        if (!$store) {
            return redirect()->route('customer.shopify.stores')->with('error', 'Store not found.');
        }

        try {
            $store->update([
                'store_name'          => $request->store_name,
                'shopify_domain'      => $request->shopify_domain,
                'webhooks_secret_key' => $request->webhooks_secret_key,
                'access_token'        => $request->access_token,
            ]);

            return redirect()->route('customer.shopify.stores')->with('success', 'Store updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating Shopify store: '.$e->getMessage());
            return redirect()->route('customer.shopify.stores')->with('error', 'An error occurred while updating the store.');
        }
    }

    /**
     * Return JSON with the import status for a given ShopifyStore.
     * This is polled from the listing blade via JS.
     */
    public function getImportStatus(ShopifyStore $store)
    {
        // Ensure the current logged-in customer owns this store
        $customer = Auth::guard('customer')->user();
        if ($store->customer_id !== $customer->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'total'    => $store->total_products,
            'imported' => $store->imported_products,
        ]);
    }




    public function products()
    {
        // Get the authenticated customer
        $customer = Auth::guard('customer')->user();

        // Retrieve Shopify store associated with the customer
        $store = ShopifyStore::where('customer_id', $customer->id)->first();

        if (!$store) {
            return redirect()->route('customer.dashboard')->with('error', 'No Shopify store found.');
        }

        // Fetch products from Shopify using the stored access token
        $shopify = new Shopify([
            'shop_url' => $store->shopify_domain,
            'access_token' => $store->access_token,
        ]);

        // Get products from Shopify
        $products = $shopify->getProducts();

        // Pass products to the view
        return view('customer.shopify.products', compact('products'));
    }

    public function stores(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        // Retrieve query parameters for search & pagination
        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 10);

        // Build a query for the customer's stores
        $query = ShopifyStore::where('customer_id', $customer->id);

        // If searching, apply filters
        if ($search) {
            $query->where(function($q) use ($search) {
                // Search these columns (adjust as needed)
                $q->where('shopify_domain', 'like', "%{$search}%")
                  ->orWhere('store_name', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Order by newest first (adjust as needed)
        $query->orderBy('id', 'desc');

        // Paginate results & preserve query string
        $stores = $query->paginate($perPage)->withQueryString();

        return view('customer.shopify.store', [
            'stores'  => $stores,
            'search'  => $search,
            'perPage' => $perPage,
        ]);
    }

 
    public function showProducts($store, Request $request)
    {
        // 1) Identify the logged-in customer
        $customer = Auth::guard('customer')->user();
    
        // 2) Retrieve the Shopify store
        $shopifyStore = ShopifyStore::where('customer_id', $customer->id)
            ->where('id', $store)
            ->first();
    
        if (!$shopifyStore) {
            return redirect()->back()->with('error', 'Store not found.');
        }
    
        // 3) Determine limit & get search term
        $limit = (int) $request->input('limit', 10);
        $validLimits = [10, 20, 50, 100];
        if (! in_array($limit, $validLimits)) {
            $limit = 10;
        }
        $searchTerm = $request->input('search', '');
    
        // 4) Build query
        $query = ShopifyProduct::where('shopify_store_id', $shopifyStore->id);
    
        if (!empty($searchTerm)) {
            // Split the search term into individual words (ignoring extra spaces)
            $searchWords = preg_split('/\s+/', $searchTerm, -1, PREG_SPLIT_NO_EMPTY);
        
            $query->where(function($q) use ($searchWords) {
                foreach ($searchWords as $word) {
                    // For each word, check if it appears in any of the columns
                    $q->where(function($q2) use ($word) {
                        $q2->where('product_title', 'like', "%{$word}%")
                           ->orWhere('variant_name', 'like', "%{$word}%")
                           ->orWhere('variant_sku', 'like', "%{$word}%");
                    });
                }
            });
        }
        
    
        // 5) Paginate the filtered products
        $localProducts = $query->orderBy('id', 'desc')
            ->paginate($limit)
            ->appends([
                'search' => $searchTerm,
                'limit'  => $limit,
            ]);
    
        // 6) If you track already-selected variants
        $alreadySelectedVariantIds = SelectedProduct::where('customer_id', $customer->id)
            ->where('shopify_store_id', $shopifyStore->id)
            ->pluck('variant_id')
            ->toArray();
    
        // 7) Use the filtered total, not the entire store’s count:
        $totalVariants = $localProducts->total(); // e.g. 700 if search matched 700
    
        // 8) Return the blade
        return view('customer.shopify.products', [
            'products'                  => $localProducts,
            'storeId'                   => $shopifyStore->id,
            'storeName'                 => $shopifyStore->store_name,
            'search'                    => $searchTerm,
            'limit'                     => $limit,
            'alreadySelectedVariantIds' => $alreadySelectedVariantIds,
            'totalVariants'             => $totalVariants, // pass the filtered total
        ]);
    }
    

/**
 * Sync newly created products/variants from Shopify Admin REST API
 * into shopify_products, including unpublished/draft products.
 * 
 * Doesn't remove or update existing rows—just inserts new ones if not present.
 */
private function syncNewShopifyProducts(ShopifyStore $store)
{
    $shopifyDomain = $store->shopify_domain;
    $accessToken   = $store->access_token;

    try {
        $client = new \GuzzleHttp\Client();

        // 1) Fetch store currency
        $shopResp = $client->get("https://{$shopifyDomain}/admin/api/2024-01/shop.json", [
            'headers' => [ 'X-Shopify-Access-Token' => $accessToken ],
        ]);
        $shopData       = json_decode($shopResp->getBody()->getContents(), true);
        $moneyFormat    = $shopData['shop']['money_format'] ?? null;
        $currencyCd     = $shopData['shop']['currency']     ?? null;
        $currencySymbol = $this->parseCurrencySymbol($moneyFormat, $currencyCd);

        // 2) Fetch store locations -> JSON
        $locResp = $client->get("https://{$shopifyDomain}/admin/api/2024-01/locations.json", [
            'headers' => [ 'X-Shopify-Access-Token' => $accessToken ],
        ]);
        $locData      = json_decode($locResp->getBody()->getContents(), true);
        $allLocations = $locData['locations'] ?? [];
        $locationIdsJson = json_encode($allLocations);

        // 3) Page-based approach from /products.json
        //    "published_status=any" => unpublished/draft are included
        //    "order=created_at desc" => newest products first
        $nextUrl = "https://{$shopifyDomain}/admin/api/2024-01/products.json?limit=250&published_status=any&order=created_at%20desc";

        while ($nextUrl) {
            Log::info("SyncNewShopify: Fetching products from: {$nextUrl}");
            $resp = $client->get($nextUrl, [
                'headers' => [ 'X-Shopify-Access-Token' => $accessToken ],
            ]);
            $data = json_decode($resp->getBody()->getContents(), true);

            $productsData = $data['products'] ?? [];
            $countProds   = count($productsData);
            Log::info("Fetched {$countProds} products on this page.");

            if (! empty($productsData)) {
                // Show sample of first product for debugging
                Log::info("First product sample: " . json_encode(array_slice($productsData, 0, 1)));
            }

            // Step A: gather inventory_item_ids
            $inventoryItemIds = [];
            foreach ($productsData as $p) {
                foreach ($p['variants'] ?? [] as $v) {
                    if (! empty($v['inventory_item_id'])) {
                        $inventoryItemIds[] = $v['inventory_item_id'];
                    }
                }
            }
            $inventoryItemIds = array_unique($inventoryItemIds);

            // Step B: fetch bulk inventory levels
            $inventoryLevels = $this->fetchInventoryLevelsBulk($shopifyDomain, $accessToken, $inventoryItemIds);

            // Build map => [ inventory_item_id => [ location_id => available ] ]
            $inventoryMap = [];
            foreach ($inventoryLevels as $lvl) {
                $iId = $lvl['inventory_item_id'] ?? null;
                $loc = $lvl['location_id']        ?? null;
                $qty = $lvl['available']          ?? 0;
                if ($iId && $loc) {
                    if (! isset($inventoryMap[$iId])) {
                        $inventoryMap[$iId] = [];
                    }
                    $inventoryMap[$iId][$loc] = $qty;
                }
            }

            $bulkInsert = [];

            // Step C: Insert new product/variant rows if not exist
            foreach ($productsData as $prod) {
                $prodId    = $prod['id']   ?? null;
                $prodTitle = $prod['title']?? '';
                $images    = $prod['images'] ?? [];

                // Map variant_ids => image
                $imgMap   = [];
                $fallback = null;
                foreach ($images as $img) {
                    if (! $fallback) {
                        $fallback = $img['src'] ?? null;
                    }
                    if (! empty($img['variant_ids'])) {
                        foreach ($img['variant_ids'] as $vId) {
                            $imgMap[$vId] = $img['src'] ?? null;
                        }
                    }
                }

                $variants = $prod['variants'] ?? [];
                foreach ($variants as $var) {
                    $varId      = $var['id']             ?? null;
                    $varSku     = $var['sku']            ?? null;
                    $varPrice   = $var['price']          ?? '0.00';
                    $varName    = $var['title']          ?? null;
                    $invItemId  = $var['inventory_item_id'] ?? null;
                    $varImage   = $imgMap[$varId]        ?? $fallback;
                    $varInventory = $inventoryMap[$invItemId] ?? [];

                    // Insert only if not present
                    $exists = ShopifyProduct::where('shopify_store_id', $store->id)
                        ->where('product_id', $prodId)
                        ->where('variant_id', $varId)
                        ->exists();
                    if ($exists) {
                        continue;
                    }

                    $bulkInsert[] = [
                        'customer_id'       => $store->customer_id,
                        'shopify_store_id'  => $store->id,
                        'product_id'        => $prodId,
                        'product_title'     => $prodTitle,
                        'variant_name'      => $varName,
                        'variant_id'        => $varId,
                        'variant_sku'       => $varSku,
                        'variant_price'     => min($varPrice, 9999999999999.99),
                        'variant_inventory' => json_encode($varInventory),
                        'location_ids'      => $locationIdsJson,
                        'variant_image'     => $varImage,
                        'currency_symbol'   => $currencySymbol,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }
            }

            if (! empty($bulkInsert)) {
                ShopifyProduct::insert($bulkInsert);
                Log::info("Inserted ".count($bulkInsert)." new records into shopify_products.");
            } else {
                Log::info("No new records to insert on this page.");
            }

            // Move to next page if present
            $linkHeader = $resp->getHeader('Link');
            $nextUrl    = $this->parseNextPageUrl($linkHeader);
            if ($nextUrl) {
                Log::info("Found next page: {$nextUrl}");
            } else {
                Log::info("No further pages. Sync likely complete.");
            }
        }

    } catch (\Exception $e) {
        Log::error("syncNewShopifyProducts error: " . $e->getMessage());
    }
}



    
    private function parseLinkHeader($header)
    {
        $links = [];

        if (!empty($header)) {
            // Split on comma: each part has URL and rel
            $parts = explode(',', $header);

            foreach ($parts as $part) {
                // Each part: <url>; rel="next"
                $section = explode(';', $part);
                if (count($section) < 2) {
                    continue;
                }

                $url = trim($section[0], '<> ');
                $rel = trim(str_replace('rel=', '', $section[1]), '" ');

                // Extract page_info query param from $url
                parse_str(parse_url($url, PHP_URL_QUERY), $queryParams);
                if (isset($queryParams['page_info'])) {
                    $links[$rel] = $queryParams['page_info'];
                }
            }
        }

        return $links;
    }

    public function addSelectedProduct(Request $request)
{
    $customer = Auth::guard('customer')->user();

    // Always expect a store_id for either single or multi-add
    $storeId = $request->input('store_id');
    if (!$storeId) {
        return redirect()->back()->with('error', 'Missing store info.');
    }

    // (Optional) check user package
    $userPackage = UserPackage::where('customer_id', $customer->id)
        ->where('status', 1)
        ->with('package')
        ->first();
    if (!$userPackage) {
        return redirect()->back()->with('error', 'No active package found. Purchase a package first.');
    }

    $packageLimit  = $userPackage->package->number_of_products;  // e.g., 10, 20, etc.
    $selectedCount = SelectedProduct::where('customer_id', $customer->id)->count();

    /**
     * ------------------------------------------------------------------
     * 1) SINGLE-VARIANT ADD (if "single_variant_add" is present)
     * ------------------------------------------------------------------
     */
    if ($request->has('single_variant_add')) {

        $productId        = $request->input('product_id');
        $productTitle     = $request->input('product_title');
        $variantId        = $request->input('variant_id');
        $variantSku       = $request->input('variant_sku');
        $variantPrice     = $request->input('variant_price');
        $variantInventory = $request->input('variant_inventory');
        $locationIdsJson  = $request->input('location_ids');
        $variant_image    = $request->input('variant_image');
        $currency_symbol  = $request->input('currency_symbol');
        $locationIdsArray = $locationIdsJson ? json_decode($locationIdsJson, true) : [];

        // Retrieve variant_name separately (can be null or empty)
        $variantName      = $request->input('variant_name');

        // Capture brand and tags
        $brand = $request->input('brand', '');
        $tags  = $request->input('tags', '');

        // Capture product_type and barcode
        $productType = $request->input('product_type', '');
        $barcode     = $request->input('barcode', '');

        if (!$productId || !$variantId) {
            return redirect()->back()->with('error', 'Missing product or variant info.');
        }

        // Check limit
        if ($selectedCount >= $packageLimit) {
            return redirect()->back()->with('error', 'You have reached your product limit of ' . $packageLimit);
        }

        // Check if this variant is already selected
        $selectedProduct = SelectedProduct::where('customer_id', $customer->id)
            ->where('variant_id', $variantId)
            ->first();
        if ($selectedProduct) {
            return redirect()->back()->with('error', 'This variant is already added.');
        }

        // Create new record including brand, tags, product_type, and barcode
        SelectedProduct::create([
            'customer_id'       => $customer->id,
            'shopify_store_id'  => $storeId,
            'product_id'        => $productId,
            'product_title'     => $productTitle,
            'variant_name'      => $variantName,
            'variant_id'        => $variantId,
            'variant_sku'       => $variantSku,
            'variant_price'     => $variantPrice,
            'variant_inventory' => $variantInventory,
            'location_ids'      => $locationIdsArray,
            'currency_symbol'   => $currency_symbol,
            'variant_image'     => $variant_image,
            'brand'             => $brand,
            'tags'              => $tags,
            'product_type'      => $productType,
            'barcode'           => $barcode,
        ]);

        return redirect()->back()->with('success', 'Variant added successfully!');

    /**
     * ------------------------------------------------------------------
     * 2) MULTI-VARIANT ADD (if "variants" array is present)
     * ------------------------------------------------------------------
     */
    } elseif ($request->has('variants')) {

        $variants      = $request->input('variants');
        $addedCount    = 0;
        $alreadyExists = 0;
        $notAddedLimit = 0;
        $exceedLimit   = false;  // track if we skipped any due to limit

        foreach ($variants as $variantId => $data) {
            // Only process if user checked "Add Selected"
            if (empty($data['checked'])) {
                continue;
            }

            // Check limit
            if ($selectedCount >= $packageLimit) {
                $notAddedLimit++;
                $exceedLimit = true;
                // Skip adding this variant, but keep checking the rest
                continue;
            }

            // Check if variant is already in DB
            $existing = SelectedProduct::where('customer_id', $customer->id)
                ->where('variant_id', $variantId)
                ->first();
            if ($existing) {
                $alreadyExists++;
                continue;
            }

            // Retrieve data from the multi-variant form
            $locationIdsArray = !empty($data['location_ids'])
                ? json_decode($data['location_ids'], true)
                : [];
            $variantName = $data['variant_name'] ?? null;

            // Capture brand and tags from multi add data
            $brand = isset($data['brand']) ? trim($data['brand']) : '';
            $tags  = isset($data['tags'])  ? trim($data['tags'])  : '';

            // Capture product_type and barcode from multi add data
            $productType = isset($data['product_type']) ? trim($data['product_type']) : '';
            $barcode     = isset($data['barcode'])      ? trim($data['barcode'])      : '';

            // Create new record including brand, tags, product_type, and barcode
            SelectedProduct::create([
                'customer_id'       => $customer->id,
                'shopify_store_id'  => $storeId,
                'product_id'        => $data['product_id']       ?? null,
                'product_title'     => $data['product_title']    ?? null,
                'variant_name'      => $variantName,
                'variant_id'        => $variantId,
                'variant_sku'       => $data['variant_sku']      ?? null,
                'currency_symbol'   => $data['currency_symbol']  ?? null,
                'variant_image'     => $data['variant_image']    ?? null,
                'variant_price'     => $data['variant_price']    ?? '0.00',
                'variant_inventory' => $data['variant_inventory'] ?? '[]',
                'location_ids'      => $locationIdsArray,
                'brand'             => $brand,
                'tags'              => $tags,
                'product_type'      => $productType,
                'barcode'           => $barcode,
            ]);

            $addedCount++;
            $selectedCount++;
        }

        // Build feedback message
        $messageParts = [];
        if ($addedCount > 0) {
            $messageParts[] = "$addedCount variant(s) added successfully";
        }
        if ($alreadyExists > 0) {
            $messageParts[] = "$alreadyExists variant(s) already existed";
        }
        if ($notAddedLimit > 0) {
            $messageParts[] = "$notAddedLimit variant(s) not added (limit reached)";
        }
        if (empty($messageParts)) {
            $messageParts[] = "No variants were selected or all were already added.";
        }

        $finalMessage = implode('. ', $messageParts) . '.';

        // If we hit the limit at any point, show it as an error; otherwise as success
        if ($exceedLimit) {
            return redirect()->back()->with('error', $finalMessage);
        } else {
            return redirect()->back()->with('success', $finalMessage);
        }

    } else {
        // If neither single variant nor multi-variant data is present
        return redirect()->back()->with('error', 'No data provided for adding variants.');
    }
}





public function fetchSelectedProducts(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $filter   = $request->get('filter');

        // 1) Build the base query for DB-level text filters
        $query = SelectedProduct::where('customer_id', $customer->id)
            ->whereHas('shopifyStore', function($q) {
                $q->where('is_master', true);
            })
            ->with('shopifyStore');

        // NEW: General search (product_title OR variant_name)
        $generalSearch = $request->get('general_search');
        if ($generalSearch) {
            $query->where(function($q) use ($generalSearch) {
                $q->where('product_title', 'like', "%{$generalSearch}%")
                  ->orWhere('variant_name', 'like', "%{$generalSearch}%");
            });
        }

        // Existing quick text filters
        $variantName = $request->get('variant_name');
        $variantSku  = $request->get('variant_sku');
        $barcode     = $request->get('barcode');

        // Apply DB-level "OR" filters for name, SKU, barcode
        if ($variantName || $variantSku || $barcode) {
            $query->where(function($q) use ($variantName, $variantSku, $barcode) {
                $hasCondition = false;

                if ($variantName) {
                    $q->where(function($q2) use ($variantName) {
                        $q2->where('variant_name', 'like', "%{$variantName}%")
                           ->orWhere('product_title', 'like', "%{$variantName}%");
                    });
                    $hasCondition = true;
                }

                if ($variantSku) {
                    if (!$hasCondition) {
                        $q->where('variant_sku', 'like', "%{$variantSku}%");
                        $hasCondition = true;
                    } else {
                        $q->orWhere('variant_sku', 'like', "%{$variantSku}%");
                    }
                }

                if ($barcode) {
                    if (!$hasCondition) {
                        $q->where('barcode', 'like', "%{$barcode}%");
                    } else {
                        $q->orWhere('barcode', 'like', "%{$barcode}%");
                    }
                }
            });
        }

        // 2) Fetch all results (unfiltered for location/brand, etc.)
        $unfilteredProducts = $query->orderBy('created_at', 'desc')->get();

        // 3) Build reference lists for the filter accordions
        $allLocations    = [];
        $allBrands       = [];
        $allProductTypes = [];
        $allTags         = [];

        foreach ($unfilteredProducts as $product) {
            // Gather locations
            $locs = is_string($product->location_ids)
                ? json_decode($product->location_ids, true)
                : $product->location_ids;
            if (is_array($locs)) {
                foreach ($locs as $loc) {
                    $locId = $loc['id'] ?? null;
                    if ($locId && !isset($allLocations[$locId])) {
                        $allLocations[$locId] = $loc['city'] ?? $loc['name'] ?? 'Unknown';
                    }
                }
            }

            // Gather brands
            if ($product->brand) {
                $allBrands[$product->brand] = $product->brand;
            }

            // Gather product types
            if ($product->product_type) {
                $allProductTypes[$product->product_type] = $product->product_type;
            }

            // Gather tags
            if ($product->tags) {
                $tagArr = explode(',', $product->tags);
                foreach ($tagArr as $t) {
                    $trimmed = trim($t);
                    if ($trimmed) {
                        $allTags[$trimmed] = $trimmed;
                    }
                }
            }
        }

        // Sort them if desired
        ksort($allLocations);
        asort($allBrands);
        asort($allProductTypes);
        asort($allTags);

        // 4) Location filter (in memory)
        $locationFilter = $request->get('locations'); // array of location IDs
        $locationFiltered = $unfilteredProducts;

        if ($locationFilter && is_array($locationFilter)) {
            $locationFiltered = $locationFiltered->filter(function($product) use ($locationFilter) {
                $inventory = is_string($product->variant_inventory)
                    ? json_decode($product->variant_inventory, true)
                    : $product->variant_inventory;
                if (!is_array($inventory)) {
                    return false;
                }
                // Keep product if ANY selected location ID is present in $inventory
                foreach ($locationFilter as $locId) {
                    if (isset($inventory[$locId])) {
                        return true;
                    }
                }
                return false;
            });
        }

        // 5) "OR" filters for stock range, brand, product type, tags, etc.
        $stockMin       = $request->get('stock_min');
        $stockMax       = $request->get('stock_max');
        $brandsFilter   = $request->input('brands', []);
        $productTypes   = $request->input('product_types', []);
        $tagsFilter     = $request->input('tags', []);
        $useLowStock    = ($filter === 'low_stock');
        $useOutOfStock  = ($filter === 'out_of_stock');

        // Check if user specified ANY of these OR filters
        $hasOrFilters = (
            $stockMin !== null ||
            $stockMax !== null ||
            !empty($brandsFilter) ||
            !empty($productTypes) ||
            !empty($tagsFilter) ||
            $useLowStock ||
            $useOutOfStock
        );

        if (!$hasOrFilters) {
            // Keep all
            $selectedProducts = $locationFiltered;
        } else {
            // Keep only those that match ANY of the conditions
            $selectedProducts = $locationFiltered->filter(function($product) use (
                $stockMin, $stockMax,
                $brandsFilter, $productTypes, $tagsFilter,
                $useLowStock, $useOutOfStock
            ) {
                $matches = false;

                // 1) stock range check
                if (!$matches && ($stockMin !== null || $stockMax !== null)) {
                    $inventory = is_string($product->variant_inventory)
                        ? json_decode($product->variant_inventory, true)
                        : $product->variant_inventory;
                    if (is_array($inventory)) {
                        foreach ($inventory as $invCount) {
                            if (
                                ($stockMin === null || $invCount >= $stockMin) &&
                                ($stockMax === null || $invCount <= $stockMax)
                            ) {
                                $matches = true;
                                break;
                            }
                        }
                    }
                }

                // 2) brands
                if (!$matches && !empty($brandsFilter)) {
                    if (in_array($product->brand, $brandsFilter)) {
                        $matches = true;
                    }
                }

                // 3) product types
                if (!$matches && !empty($productTypes)) {
                    if (in_array($product->product_type, $productTypes)) {
                        $matches = true;
                    }
                }

                // 4) tags
                if (!$matches && !empty($tagsFilter)) {
                    if ($product->tags) {
                        $tagArr = explode(',', $product->tags);
                        $tagArr = array_map('trim', $tagArr);
                        if (count(array_intersect($tagArr, $tagsFilter)) > 0) {
                            $matches = true;
                        }
                    }
                }

                // 5) low_stock
                if (!$matches && $useLowStock) {
                    $inventory = is_string($product->variant_inventory)
                        ? json_decode($product->variant_inventory, true)
                        : $product->variant_inventory;
                    if (is_array($inventory)) {
                        // If ANY location is <= 5, we match
                        foreach ($inventory as $invCount) {
                            if ($invCount <= 5) {
                                $matches = true;
                                break;
                            }
                        }
                    }
                }

                // 6) out_of_stock
                if (!$matches && $useOutOfStock) {
                    $inventory = is_string($product->variant_inventory)
                        ? json_decode($product->variant_inventory, true)
                        : $product->variant_inventory;
                    if (is_array($inventory)) {
                        foreach ($inventory as $invCount) {
                            if ($invCount == 0) {
                                $matches = true;
                                break;
                            }
                        }
                    }
                }

                return $matches;
            });
        }

        // SERVER-SIDE PAGINATION
        $selectedProducts = $selectedProducts->values(); // reindex
        $perPage = $request->input('per_page', 10);
        $page    = $request->input('page', 1);
        $offset  = ($page - 1) * $perPage;

        // Slice the collection to get items for the current page
        $itemsForCurrentPage = $selectedProducts->slice($offset, $perPage)->values();

        // Create a LengthAwarePaginator instance
        $paginatedSelectedProducts = new LengthAwarePaginator(
            $itemsForCurrentPage,
            $selectedProducts->count(), // total items
            $perPage,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('customer.inventory.index', [
            'selectedProducts' => $paginatedSelectedProducts,
            'filter'           => $filter,
            'allLocations'     => $allLocations,
            'allBrands'        => $allBrands,
            'allProductTypes'  => $allProductTypes,
            'allTags'          => $allTags,
        ]);
    }






    public function syncNowV2($storeId)
    {
        $store = ShopifyStore::find($storeId);
        if (! $store) {
            return response()->json(['error' => 'Store not found'], 404);
        }

        // Dispatch the new job
        ImportShopifyProductsJobV2::dispatch($store->id, $store->customer_id);

        return response()->json(['message' => 'Sync job (V2) dispatched']);
    }

    public function getImportStatus2($storeId)
    {
        $store = ShopifyStore::find($storeId);
        if (! $store) {
            return response()->json(['error' => 'Store not found'], 404);
        }

        // Return JSON with total_products & imported_products
        return response()->json([
            'total'    => (int) $store->total_products,
            'imported' => (int) $store->imported_products,
        ]);
    }
    
    public function destroyStore($storeId)
{
    $customer = Auth::guard('customer')->user();

    // Find the store that belongs to this customer
    $shopifyStore = ShopifyStore::where('customer_id', $customer->id)
        ->where('id', $storeId)
        ->first();

    if (!$shopifyStore) {
        return redirect()->route('customer.shopify.stores')
                        ->with('error', 'Store not found or no permission.');
    }

    try {
        // 1) Remove all selected products referencing this store
        SelectedProduct::where('shopify_store_id', $shopifyStore->id)->delete();

        // 2) Remove products from shopify_products table
        ShopifyProduct::where('shopify_store_id', $shopifyStore->id)->delete();

        // 3) Permanently delete the store record
        $shopifyStore->delete();

        return redirect()->route('customer.shopify.stores')
            ->with('success', 'Store permanently deleted along with related products.');
    } catch (\Exception $e) {
        \Log::error('Error removing store: '.$e->getMessage());
        return redirect()->route('customer.shopify.stores')
            ->with('error', 'Failed to remove the store.');
    }
}


    public function deleteSelectedProduct($id)
    {
        $customer = Auth::guard('customer')->user();

        // Find the selected product belonging to the user
        $selectedProduct = SelectedProduct::where('customer_id', $customer->id)
            ->where('id', $id)
            ->first();

        if (!$selectedProduct) {
            return redirect()->route('customer.shopify.fetchSelectedProducts')
                ->with('error', 'Product not found or no permission.');
        }

        try {
            $selectedProduct->delete();

            return redirect()->route('customer.shopify.fetchSelectedProducts')
                ->with('success', 'Product removed from your inventory.');
        } catch (\Exception $e) {
            \Log::error('Error removing selected product: ' . $e->getMessage());
            return redirect()->route('customer.shopify.fetchSelectedProducts')
                ->with('error', 'Failed to remove product.');
        }
    }

    public function deleteMultipleSelectedProducts(Request $request)
    {

        
        $customer = Auth::guard('customer')->user();

        // Get the array of selected product IDs from the form
        $selectedIds = $request->input('selected_products', []);
    
        if (empty($selectedIds)) {
            // No products were checked
            return redirect()
                ->route('customer.shopify.fetchSelectedProducts')
                ->with('error', 'No products selected for deletion.');
        }

        try {
            // Attempt to delete all matching products belonging to this customer
            $deletedCount = SelectedProduct::where('customer_id', $customer->id)
                ->whereIn('id', $selectedIds)
                ->delete();

            if ($deletedCount > 0) {
                return redirect()
                    ->route('customer.shopify.fetchSelectedProducts')
                    ->with('success', "Successfully deleted $deletedCount products.");
            } else {
                return redirect()
                    ->route('customer.shopify.fetchSelectedProducts')
                    ->with('error', 'No matching products found or no permission.');
            }
        } catch (\Exception $e) {
            \Log::error('Error during bulk deletion: ' . $e->getMessage());
            return redirect()
                ->route('customer.shopify.fetchSelectedProducts')
                ->with('error', 'An error occurred while deleting selected products.');
        }
    }

    public function updateInventory(Request $request)
{
    $customer = Auth::guard('customer')->user();

    $selectedProductId = $request->input('selected_product_id');
    $variantId         = $request->input('variant_id');
    $shopifyStoreId    = $request->input('shopify_store_id');
    $operation         = $request->input('inventory_operation'); // 'set' or 'adjust'
    $qty               = (int) $request->input('inventory_quantity', 0);
    $locationId        = $request->input('location_id'); // chosen location ID in the Blade

    // 1) Check if the product belongs to the current user
    $selectedProduct = SelectedProduct::where('customer_id', $customer->id)
        ->where('id', $selectedProductId)
        ->with('shopifyStore')
        ->first();

    if (!$selectedProduct) {
        return redirect()->route('customer.shopify.fetchSelectedProducts')
            ->with('error', 'Product not found or no permission.');
    }
    if ($selectedProduct->shopify_store_id != $shopifyStoreId) {
        return redirect()->route('customer.shopify.fetchSelectedProducts')
            ->with('error', 'Mismatch in store ID. Operation not allowed.');
    }

    // 2) Check the actual ShopifyStore
    $shopifyStore = ShopifyStore::where('customer_id', $customer->id)
        ->where('id', $shopifyStoreId)
        ->first();
    if (!$shopifyStore) {
        return redirect()->route('customer.shopify.fetchSelectedProducts')
            ->with('error', 'Shopify store record not found.');
    }

    try {
        // 3) Parse the existing variant_inventory as JSON
        $existingInv = $selectedProduct->variant_inventory;
        if (is_string($existingInv)) {
            $existingInv = json_decode($existingInv, true);
        }
        if (!is_array($existingInv)) {
            $existingInv = []; // fallback if null or invalid
        }

        // 4) Current local inventory for the chosen $locationId (master store’s location)
        $currentLocalInvForLocation = (int)($existingInv[$locationId] ?? 0);

        // 5) Calculate the new local value
        $newLocalInvForLocation = ($operation === 'set')
            ? $qty
            : ($currentLocalInvForLocation + $qty);

        if ($newLocalInvForLocation < 0) {
            return redirect()->route('customer.shopify.fetchSelectedProducts')
                ->with('error', 'Inventory cannot go below zero.');
        }

        // 6) Update the master product's JSON for that one location
        $existingInv[$locationId] = $newLocalInvForLocation;
        $selectedProduct->variant_inventory = json_encode($existingInv);
        $selectedProduct->save();

        // 7) Update Shopify for this master product
        $this->updateShopifyVariantInventory(
            $shopifyStore,
            $variantId,
            $operation,
            $qty,
            $currentLocalInvForLocation,
            $locationId
        );

        // 8) If this product belongs to a MASTER store, update all linked child products
        if ($selectedProduct->shopifyStore && $selectedProduct->shopifyStore->is_master) {
            // Find all links containing $selectedProduct->id
            $linkedRecords = LinkedProduct::where('product_one_id', $selectedProduct->id)
                ->orWhere('product_two_id', $selectedProduct->id)
                ->get();

            foreach ($linkedRecords as $link) {
                // Figure out which side is the child
                $childProductId = ($link->product_one_id == $selectedProduct->id)
                    ? $link->product_two_id
                    : $link->product_one_id;

                // Fetch the child product
                $childProduct = SelectedProduct::where('id', $childProductId)
                    ->with('shopifyStore')
                    ->first();
                if (!$childProduct) {
                    continue; // skip if missing
                }

                // (a) Parse the child's current variant_inventory
                $childInv = $childProduct->variant_inventory;
                if (is_string($childInv)) {
                    $childInv = json_decode($childInv, true);
                }
                if (!is_array($childInv)) {
                    $childInv = [];
                }

                // ---------------------------------------------------------------------
                // ******* KEY CHANGE HERE *******
                //
                // Instead of using the master's $locationId, we pick the child's
                // *own* location. If there's exactly one location key, we update *that*.
                // If your child has multiple keys, you need a more robust mapping. For now,
                // we just pick the first child location key.
                // ---------------------------------------------------------------------

                $childLocationId = array_key_first($childInv);

                // If for some reason the child has no location IDs, skip
                if (!$childLocationId) {
                    continue;
                }

                // (b) Find the current inventory in the child's location
                $childCurrentLocal = (int)($childInv[$childLocationId] ?? 0);

                // (c) Calculate new child local inventory
                $childNewLocal = ($operation === 'set')
                    ? $qty
                    : ($childCurrentLocal + $qty);

                if ($childNewLocal < 0) {
                    $childNewLocal = 0; // or handle differently
                }

                // (d) Overwrite the child's location with the new quantity
                $childInv[$childLocationId] = $childNewLocal;
                $childProduct->variant_inventory = json_encode($childInv);
                $childProduct->save();

                // (e) Update the child's Shopify store, if it exists
                $childVariantId = $childProduct->variant_id;
                if ($childProduct->shopifyStore) {
                    // We'll pass the child's location ID to Shopify.
                    // If the child's location ID is actually *different* in Shopify,
                    // you may need a real location mapping. This is a minimal example.
                    $this->updateShopifyVariantInventory(
                        $childProduct->shopifyStore,
                        $childVariantId,
                        $operation,
                        $qty,
                        $childCurrentLocal,
                        $childLocationId
                    );
                }
            }
        }

        return redirect()->route('customer.shopify.fetchSelectedProducts')
            ->with('success', 'Inventory updated successfully (master + linked child products)!');
    } catch (\Exception $e) {
        \Log::error('Error updating inventory: ' . $e->getMessage());
        return redirect()->route('customer.shopify.fetchSelectedProducts')
            ->with('error', 'Failed to update inventory. ' . $e->getMessage());
    }
}


    private function updateShopifyVariantInventory(
        $shopifyStore,
        $variantId,
        $operation, // 'set' or 'adjust'
        $qty,
        $currentLocalInv,
        $locationId
    ) {
        \Log::info("ENTERING updateShopifyVariantInventory", [
            'shopifyStoreDomain' => $shopifyStore->shopify_domain,
            'variantId'          => $variantId,
            'operation'          => $operation,
            'qty'                => $qty,
            'currentLocalInv'    => $currentLocalInv,
            'locationId_param'   => $locationId,
        ]);

        // Shopify GraphQL requires a "gid://shopify/Location/..." format
        if (!str_starts_with($locationId, 'gid://shopify/Location/')) {
            $locationId = "gid://shopify/Location/{$locationId}";
        }
        \Log::info("Converted locationId to GID if needed", ['locationId' => $locationId]);

        $variantGid  = "gid://shopify/ProductVariant/{$variantId}";
        $shopDomain  = $shopifyStore->shopify_domain;
        $accessToken = $shopifyStore->access_token;

        // 1) Fetch the InventoryItem ID for this variant
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

        $client = new Client();
        $url    = "https://{$shopDomain}/admin/api/2023-10/graphql.json";

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

        $data1 = json_decode($resp1->getBody()->getContents(), true);
        \Log::info("Response from first GraphQL (productVariant)", ['data1' => $data1]);

        $inventoryItemId = $data1['data']['productVariant']['inventoryItem']['id'] ?? null;
        $isTracked       = $data1['data']['productVariant']['inventoryItem']['tracked'] ?? false;

        if (!$inventoryItemId) {
            throw new \Exception("No inventoryItemId found for variant=$variantId");
        }
        if (!$isTracked) {
            throw new \Exception("Variant=$variantId is not 'tracked'. Enable Shopify inventory tracking in admin.");
        }

        // 2) Build the GraphQL mutation to set or adjust
        if ($operation === 'set') {
            // inventorySetOnHandQuantities
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
            // inventoryAdjustQuantities
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
                    'name' => 'available',
                    'reason' => 'other',
                    'changes' => [[
                        'inventoryItemId' => $inventoryItemId,
                        'locationId'      => $locationId,
                        'delta'           => $qty
                    ]]
                ]
            ];
        }

        \Log::info("Sending second GraphQL to update inventory", [
            'operation' => $operation,
            'mutation'  => $mutation,
            'variables' => $vars2
        ]);

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

        $data2 = json_decode($resp2->getBody()->getContents(), true);
        \Log::info("Response from second GraphQL (set/adjust)", ['data2' => $data2]);

        // 3) Check top-level GraphQL errors
        if (!empty($data2['errors'])) {
            \Log::error("Top-level GraphQL errors", ['errors' => $data2['errors']]);
            throw new \Exception('Shopify top-level error: ' . json_encode($data2['errors']));
        }

        // 4) Check userErrors
        if ($operation === 'set') {
            $userErrors = $data2['data']['inventorySetOnHandQuantities']['userErrors'] ?? [];
        } else {
            $userErrors = $data2['data']['inventoryAdjustQuantities']['userErrors'] ?? [];
        }

        if (!empty($userErrors)) {
            \Log::error("Shopify userErrors found", ['userErrors' => $userErrors]);
            throw new \Exception('Shopify Inventory Error: ' . json_encode($userErrors));
        }

        \Log::info("DONE updating inventory for variant=$variantId, operation=$operation, qty=$qty");
    }
}