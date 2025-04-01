<?php

namespace App\Jobs;

use App\Models\ShopifyStore;
use App\Models\ShopifyProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class ImportShopifyProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $storeId;
    protected $customerId;

    protected $batchSize = 500;
    protected $inventoryBatchSize = 50;

    public function __construct(ShopifyStore $store, int $customerId)
    {
        $this->storeId = $store->id;
        $this->customerId = $customerId;
    }

    public function handle()
    {
        set_time_limit(0);

        try {
            $store = ShopifyStore::find($this->storeId);
            if (!$store) {
                Log::error("Store with id {$this->storeId} not found.");
                return;
            }

            $shopifyDomain = $store->shopify_domain;
            $accessToken   = $store->access_token;

            // Reset progress counters if both total_products and imported_products are zero.
            if ($store->total_products == 0 && $store->imported_products == 0) {
                Log::info("Resetting progress counters for store ID {$store->id}");
                $store->update([
                    'total_products'    => 0,
                    'imported_products' => 0,
                ]);
            } else {
                Log::info("Store ID {$store->id} already has progress; skipping reset.");
            }

            // 1) Count total variants
            $totalVariantCount = $this->countAllVariants($shopifyDomain, $accessToken);
            $store->update(['total_products' => $totalVariantCount]);

            // 2) Fetch shop currency info
            $shopResponse = $this->safeGet(
                "https://{$shopifyDomain}/admin/api/2024-01/shop.json",
                ['headers' => ['X-Shopify-Access-Token' => $accessToken]]
            );
            $shopData       = json_decode($shopResponse->getBody()->getContents(), true);
            $moneyFormat    = $shopData['shop']['money_format'] ?? null;
            $currencyCode   = $shopData['shop']['currency'] ?? null;
            $currencySymbol = $this->parseCurrencySymbol($moneyFormat, $currencyCode);

            // 3) Fetch store locations
            $locationsResp  = $this->safeGet(
                "https://{$shopifyDomain}/admin/api/2024-01/locations.json",
                ['headers' => ['X-Shopify-Access-Token' => $accessToken]]
            );
            $locationsData   = json_decode($locationsResp->getBody()->getContents(), true);
            $allLocations    = $locationsData['locations'] ?? [];
            $locationIdsJson = json_encode($allLocations);

            // 4) Start importing products (pages of 100)
            $nextPage = "https://{$shopifyDomain}/admin/api/2024-01/products.json?limit=100";
            $pageCount = 0;

            while ($nextPage) {
                $pageCount++;
                Log::info("Processing page {$pageCount}: URL: {$nextPage}");
                $response = $this->safeGet($nextPage, [
                    'headers' => ['X-Shopify-Access-Token' => $accessToken],
                ]);
                $data     = json_decode($response->getBody()->getContents(), true);
                $products = $data['products'] ?? [];

                // Collect variant IDs for this page
                $pageVariantIds = [];
                foreach ($products as $product) {
                    foreach ($product['variants'] ?? [] as $variant) {
                        if (!empty($variant['id'])) {
                            $pageVariantIds[] = $variant['id'];
                        }
                    }
                }
                $pageVariantIds = array_unique($pageVariantIds);

                // Find already-inserted variants in DB
                $existingVariants = ShopifyProduct::where('shopify_store_id', $store->id)
                    ->whereIn('variant_id', $pageVariantIds)
                    ->pluck('variant_id')
                    ->toArray();
                $existingVariants = array_flip($existingVariants);

                // Gather inventory_item_ids for bulk inventory fetching
                $inventoryItemIds = [];
                foreach ($products as $product) {
                    foreach ($product['variants'] ?? [] as $v) {
                        if (!empty($v['inventory_item_id'])) {
                            $inventoryItemIds[] = $v['inventory_item_id'];
                        }
                    }
                }
                $inventoryItemIds = array_unique($inventoryItemIds);

                // Fetch inventory levels in bulk
                $inventoryLevels = $this->fetchInventoryLevelsBulk($shopifyDomain, $accessToken, $inventoryItemIds);

                $bulkInsertData = [];

                foreach ($products as $product) {
                    $productId    = $product['id'] ?? null;
                    $productTitle = $product['title'] ?? '';
                    $images       = $product['images'] ?? [];
                    $variants     = $product['variants'] ?? [];

                    // Retrieve brand, tags, and product_type from product JSON.
                    // Fallback to 'UNKNOWN' for brand if missing, and empty string for tags and product_type.
                    $brand       = (isset($product['vendor']) && trim($product['vendor']) !== '')
                        ? trim($product['vendor'])
                        : 'UNKNOWN';
                    $tags        = isset($product['tags'])
                        ? trim($product['tags'])
                        : '';
                    $productType = isset($product['product_type'])
                        ? trim($product['product_type'])
                        : '';

                    Log::info("Product ID {$productId} - Brand: {$brand}, Tags: " . ($tags ?: 'EMPTY') . ", Product Type: " . ($productType ?: 'EMPTY'));

                    // Determine fallback image & build image map for variants
                    $fallbackImage = null;
                    $imageMap      = [];
                    foreach ($images as $img) {
                        if (!$fallbackImage && isset($img['src'])) {
                            $fallbackImage = $img['src'];
                        }
                        if (!empty($img['variant_ids'])) {
                            foreach ($img['variant_ids'] as $vId) {
                                $imageMap[$vId] = $img['src'];
                            }
                        }
                    }

                    // Process each variant in the product
                    foreach ($variants as $variant) {
                        $variantId = $variant['id'] ?? null;
                        if (!$variantId) {
                            Log::info("Skipping variant with missing ID in product {$productId}");
                            continue;
                        }

                        // Extract barcode from the variant data
                        $barcode = isset($variant['barcode']) ? trim($variant['barcode']) : '';

                        // If variant already exists, update its brand, tags, product_type, and barcode
                        if (isset($existingVariants[$variantId])) {
                            Log::info("Updating variant ID {$variantId} with Brand: {$brand}, Tags: " . ($tags ?: 'EMPTY') . ", Product Type: " . ($productType ?: 'EMPTY') . ", Barcode: " . ($barcode ?: 'EMPTY'));
                            ShopifyProduct::where('shopify_store_id', $store->id)
                                ->where('variant_id', $variantId)
                                ->update([
                                    'brand'        => $brand,
                                    'tags'         => $tags,
                                    'product_type' => $productType,
                                    'barcode'      => $barcode,
                                ]);
                            ShopifyStore::where('id', $store->id)
                                ->increment('imported_products', 1);
                            continue;
                        }

                        $variantImage   = $imageMap[$variantId] ?? $fallbackImage;
                        $inventoryArray = $inventoryLevels[$variant['inventory_item_id']] ?? [];

                        Log::info("Inserting variant ID {$variantId} with Brand: {$brand}, Tags: " . ($tags ?: 'EMPTY') . ", Product Type: " . ($productType ?: 'EMPTY') . ", Barcode: " . ($barcode ?: 'EMPTY'));

                        // Build insert data including brand, tags, product_type, and barcode
                        $bulkInsertData[] = [
                            'customer_id'       => $this->customerId,
                            'shopify_store_id'  => $store->id,
                            'product_id'        => $productId,
                            'product_title'     => $productTitle,
                            'variant_name'      => $variant['title'] ?? null,
                            'variant_id'        => $variantId,
                            'variant_sku'       => $variant['sku']  ?? null,
                            'variant_price'     => min($variant['price'] ?? '0.00', 9999999999999.99),
                            'variant_inventory' => json_encode($inventoryArray),
                            'location_ids'      => $locationIdsJson,
                            'variant_image'     => $variantImage,
                            'currency_symbol'   => $currencySymbol,
                            'brand'             => $brand,
                            'tags'              => $tags,
                            'product_type'      => $productType,
                            'barcode'           => $barcode,
                            'created_at'        => now(),
                            'updated_at'        => now(),
                        ];

                        // When batch size is reached, insert and reset bulkInsertData
                        if (count($bulkInsertData) >= $this->batchSize) {
                            ShopifyProduct::insert($bulkInsertData);
                            $insertedCount = count($bulkInsertData);
                            ShopifyStore::where('id', $store->id)
                                ->increment('imported_products', $insertedCount);
                            Log::info("Inserted batch of {$insertedCount} variants including brand, tags, product_type, and barcode.");
                            $bulkInsertData = [];
                        }
                    }
                }

                // Insert any leftover variants
                if (!empty($bulkInsertData)) {
                    ShopifyProduct::insert($bulkInsertData);
                    $insertedCount = count($bulkInsertData);
                    ShopifyStore::where('id', $store->id)
                        ->increment('imported_products', $insertedCount);
                    Log::info("Inserted leftover batch of {$insertedCount} variants including brand, tags, product_type, and barcode.");
                    $bulkInsertData = [];
                }

                // Determine next page from the Link header
                $linkHeader = $response->getHeader('Link');
                $nextPage   = $this->parseNextPageUrl($linkHeader);
            }

            Log::info("=== Import completed for store ID {$store->id} ===");
        } catch (\Exception $e) {
            Log::error("Error importing Shopify products for store ID {$this->storeId}: " . $e->getMessage());
        }
    }

    private function countAllVariants($domain, $token)
    {
        $total = 0;
        $page = 0;
        $url   = "https://{$domain}/admin/api/2024-01/products.json?limit=100";

        while ($url) {
            $page++;
            $res = $this->safeGet($url, [
                'headers' => ['X-Shopify-Access-Token' => $token],
            ]);
            $json     = json_decode($res->getBody()->getContents(), true);
            $products = $json['products'] ?? [];
            $pageCount = 0;
            foreach ($products as $p) {
                $variants = $p['variants'] ?? [];
                $pageCount += count($variants);
            }
            $total += $pageCount;
            $linkHeader = $res->getHeader('Link');
            $url        = $this->parseNextPageUrl($linkHeader);
        }
        return $total;
    }

    private function fetchInventoryLevelsBulk($shopifyDomain, $accessToken, $inventoryItemIds)
    {
        if (empty($inventoryItemIds)) {
            return [];
        }

        $inventoryLevels = [];
        $chunks = array_chunk($inventoryItemIds, $this->inventoryBatchSize);

        foreach ($chunks as $batch) {
            $url = "https://{$shopifyDomain}/admin/api/2024-01/inventory_levels.json?inventory_item_ids=" 
                    . implode(',', $batch);

            $resp = $this->safeGet($url, [
                'headers' => ['X-Shopify-Access-Token' => $accessToken],
            ]);
            $data = json_decode($resp->getBody()->getContents(), true);

            foreach (($data['inventory_levels'] ?? []) as $lvl) {
                $iid = $lvl['inventory_item_id'] ?? null;
                $lid = $lvl['location_id']       ?? null;
                $qty = $lvl['available']         ?? 0;
                if ($iid && $lid !== null) {
                    $inventoryLevels[$iid][$lid] = $qty;
                }
            }
        }
        return $inventoryLevels;
    }

    private function parseNextPageUrl($linkHeader)
    {
        if (empty($linkHeader)) {
            return null;
        }
        foreach (explode(',', $linkHeader[0]) as $part) {
            if (strpos($part, 'rel="next"') !== false) {
                $nextPage = (preg_match('/<(.*)>/', $part, $matches)) ? $matches[1] : null;
                return $nextPage;
            }
        }
        return null;
    }

    private function parseCurrencySymbol(?string $moneyFormat, ?string $currencyCode): ?string
    {
        if ($moneyFormat) {
            $placeholders = [
                '{{amount}}',
                '{{amount_no_decimals}}',
                '{{amount_with_comma_separator}}'
            ];
            $symbol = trim(str_replace($placeholders, '', $moneyFormat));
            if ($symbol !== strip_tags($symbol)) {
                $symbol = strip_tags($symbol);
            }
            if (!empty($symbol)) {
                return $symbol;
            }
        }
        $currencyMap = [
            'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'INR' => '₹',
            'JPY' => '¥', 'CAD' => 'C$', 'AUD' => 'A$', 'SGD' => 'S$',
            'CHF' => 'CHF',
        ];
        return $currencyMap[$currencyCode] ?? $currencyCode;
    }

    private function safeGet(string $url, array $options = [], $attempt = 0)
    {
        usleep(500_000);

        try {
            $client = new Client();
            return $client->get($url, $options);
        } catch (ClientException $e) {
            if ($e->getCode() === 429 && $attempt < 5) {
                $retryAfter = $e->getResponse()->getHeader('Retry-After');
                $retryDelay = !empty($retryAfter) ? (int)$retryAfter[0] : 3;
                sleep($retryDelay);
                return $this->safeGet($url, $options, $attempt + 1);
            }
            throw $e;
        }
    }
}
