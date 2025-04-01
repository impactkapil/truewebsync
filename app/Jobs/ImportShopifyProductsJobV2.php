<?php

namespace App\Jobs;

use App\Models\ShopifyStore;
use App\Models\ShopifyProduct;
use App\Models\SelectedProduct;   // Make sure these models exist
use App\Models\LinkedProduct;     // Make sure this model exists
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class ImportShopifyProductsJobV2 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $storeId;     // integer store ID
    protected $customerId;  // integer customer ID

    protected $batchSize = 500;
    protected $inventoryBatchSize = 50;

    /**
     * Accept int $storeId instead of ShopifyStore $store.
     */
    public function __construct(int $storeId, int $customerId)
    {
        $this->storeId    = $storeId;
        $this->customerId = $customerId;
    }

    public function handle()
    {
        set_time_limit(0);

        try {
            // 1) Load the store from the integer $this->storeId
            $store = ShopifyStore::find($this->storeId);
            if (!$store) {
                Log::error("[V2] Store with id {$this->storeId} not found.");
                return;
            }

            $shopifyDomain = $store->shopify_domain;
            $accessToken   = $store->access_token;

            // 2) RESET: always start from zero so we don't add onto old values
            $store->update([
                'total_products'    => 0,
                'imported_products' => 0,
            ]);

            // 3) Count total variants
            $totalVariantCount = $this->countAllVariants($shopifyDomain, $accessToken);
            $store->update(['total_products' => $totalVariantCount]);

            // 4) Fetch currency info (same logic as V1 job)
            $shopResponse = $this->safeGet(
                "https://{$shopifyDomain}/admin/api/2024-01/shop.json",
                ['headers' => ['X-Shopify-Access-Token' => $accessToken]]
            );
            $shopData       = json_decode($shopResponse->getBody()->getContents(), true);
            $moneyFormat    = $shopData['shop']['money_format'] ?? null;
            $currencyCode   = $shopData['shop']['currency'] ?? null;
            $currencySymbol = $this->parseCurrencySymbol($moneyFormat, $currencyCode);

            // 5) Fetch store locations
            $locationsResp  = $this->safeGet(
                "https://{$shopifyDomain}/admin/api/2024-01/locations.json",
                ['headers' => ['X-Shopify-Access-Token' => $accessToken]]
            );
            $locationsData   = json_decode($locationsResp->getBody()->getContents(), true);
            $allLocations    = $locationsData['locations'] ?? [];
            $locationIdsJson = json_encode($allLocations);

            // 6) Import
            $nextPage        = "https://{$shopifyDomain}/admin/api/2024-01/products.json?limit=100";
            $pageCount       = 0;
            $importedSoFar   = 0;     // local counter
            $validVariantIds = [];    // will hold ALL variant_ids we find on Shopify

            while ($nextPage) {
                $pageCount++;
                Log::info("[V2] Processing page {$pageCount}: {$nextPage}");

                $response = $this->safeGet($nextPage, [
                    'headers' => ['X-Shopify-Access-Token' => $accessToken],
                ]);
                $data     = json_decode($response->getBody()->getContents(), true);
                $products = $data['products'] ?? [];

                // Collect variant IDs for this page
                foreach ($products as $product) {
                    foreach ($product['variants'] ?? [] as $variant) {
                        if (!empty($variant['id'])) {
                            $validVariantIds[] = $variant['id'];
                        }
                    }
                }

                // If you want to update existing variants' brand/tags, same as V1:
                $pageVariantIds = [];
                foreach ($products as $product) {
                    foreach ($product['variants'] ?? [] as $variant) {
                        if (!empty($variant['id'])) {
                            $pageVariantIds[] = $variant['id'];
                        }
                    }
                }
                $pageVariantIds = array_unique($pageVariantIds);

                // Find which variants already exist in DB
                $existingVariants = ShopifyProduct::where('shopify_store_id', $store->id)
                    ->whereIn('variant_id', $pageVariantIds)
                    ->pluck('variant_id')
                    ->toArray();
                $existingVariants = array_flip($existingVariants);

                // Gather inventory item IDs
                $inventoryItemIds = [];
                foreach ($products as $product) {
                    foreach ($product['variants'] ?? [] as $v) {
                        if (!empty($v['inventory_item_id'])) {
                            $inventoryItemIds[] = $v['inventory_item_id'];
                        }
                    }
                }
                $inventoryItemIds = array_unique($inventoryItemIds);

                // Fetch inventory in bulk
                $inventoryLevels = $this->fetchInventoryLevelsBulk($shopifyDomain, $accessToken, $inventoryItemIds);
                $bulkInsertData  = [];

                // Process products & variants
                foreach ($products as $product) {
                    $productId    = $product['id']    ?? null;
                    $productTitle = $product['title'] ?? '';
                    $images       = $product['images'] ?? [];
                    $variants     = $product['variants'] ?? [];

                    $brand = (isset($product['vendor']) && trim($product['vendor']) !== '')
                        ? trim($product['vendor'])
                        : 'UNKNOWN';
                    $tags        = isset($product['tags']) ? trim($product['tags']) : '';
                    $productType = isset($product['product_type']) ? trim($product['product_type']) : '';

                    // fallback image & image map
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

                    foreach ($variants as $variant) {
                        $variantId = $variant['id'] ?? null;
                        if (!$variantId) {
                            Log::info("[V2] Skipping variant with missing ID (prod {$productId})");
                            continue;
                        }

                        $barcode = isset($variant['barcode']) ? trim($variant['barcode']) : '';

                        // If variant exists => update
                        if (isset($existingVariants[$variantId])) {
                            ShopifyProduct::where('shopify_store_id', $store->id)
                                ->where('variant_id', $variantId)
                                ->update([
                                    'brand'        => $brand,
                                    'tags'         => $tags,
                                    'product_type' => $productType,
                                    'barcode'      => $barcode,
                                ]);

                            $importedSoFar += 1;
                            $store->update(['imported_products' => $importedSoFar]);
                            continue;
                        }

                        // Insert new variant
                        $variantImage   = $imageMap[$variantId] ?? $fallbackImage;
                        $inventoryArray = $inventoryLevels[$variant['inventory_item_id']] ?? [];

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

                        // Batch insert
                        if (count($bulkInsertData) >= $this->batchSize) {
                            ShopifyProduct::insert($bulkInsertData);

                            $insertedCount = count($bulkInsertData);
                            $importedSoFar += $insertedCount;
                            $store->update(['imported_products' => $importedSoFar]);

                            Log::info("[V2] Inserted batch of {$insertedCount} variants");
                            $bulkInsertData = [];
                        }
                    }
                }

                // Insert any leftover variants in the batch
                if (!empty($bulkInsertData)) {
                    ShopifyProduct::insert($bulkInsertData);

                    $insertedCount = count($bulkInsertData);
                    $importedSoFar += $insertedCount;
                    $store->update(['imported_products' => $importedSoFar]);

                    Log::info("[V2] Inserted leftover batch of {$insertedCount} variants");
                    $bulkInsertData = [];
                }

                // Move to next page if any
                $linkHeader = $response->getHeader('Link');
                $nextPage   = $this->parseNextPageUrl($linkHeader);
            }

            // ------------------------------------------------------------------
            // 7) Cleanup step: Delete any local variants/products not in Shopify
            // ------------------------------------------------------------------
            // Make unique array of all valid variant IDs
            $validVariantIds = array_unique($validVariantIds);

            Log::info("[V2] Starting cleanup for missing variants...");

            // (A) Find all selected_products that have a 'variant_id' not in Shopify
            //     i.e. missing from $validVariantIds
            $allMissingSelectedIds = SelectedProduct::whereNotIn('variant_id', $validVariantIds)
                ->pluck('id');

            // (B) From these, find which ones are used in product_two_id of linked_products
            //     We DO NOT want to delete them from selected_products in that case
            $usedAsTwoIds = LinkedProduct::whereIn('product_two_id', $allMissingSelectedIds)
                ->pluck('product_two_id');

            // (C) The final set we *will* delete from selected_products
            //     => those missing from Shopify AND *not* used in product_two_id
            $finalDeleteIds = $allMissingSelectedIds->diff($usedAsTwoIds);

            // (D) Remove from linked_products *only* where product_one_id is in that final set
            //     (User specifically does *not* want to remove rows if used in product_two_id)
            LinkedProduct::whereIn('product_one_id', $finalDeleteIds)->delete();

            // (E) Now remove those from selected_products
            SelectedProduct::whereIn('id', $finalDeleteIds)->delete();

            // (F) Finally, remove missing variants from shopify_products
            ShopifyProduct::where('shopify_store_id', $store->id)
                ->whereNotIn('variant_id', $validVariantIds)
                ->delete();

            Log::info("[V2] Cleanup completed. Missing variants have been deleted.");
            Log::info("[V2] === Import completed for store ID {$store->id} ===");

        } catch (\Exception $e) {
            Log::error("[V2] Error importing for store ID {$this->storeId}: " . $e->getMessage());
        }
    }

    /**
     * Count total variants
     */
    private function countAllVariants($domain, $token)
    {
        $total = 0;
        $url   = "https://{$domain}/admin/api/2024-01/products.json?limit=100";

        while ($url) {
            $res = $this->safeGet($url, [
                'headers' => ['X-Shopify-Access-Token' => $token],
            ]);
            $json     = json_decode($res->getBody()->getContents(), true);
            $products = $json['products'] ?? [];

            foreach ($products as $p) {
                $variants = $p['variants'] ?? [];
                $total += count($variants);
            }
            $linkHeader = $res->getHeader('Link');
            $url        = $this->parseNextPageUrl($linkHeader);
        }

        return $total;
    }

    /**
     * Bulk inventory fetch
     */
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

    /**
     * Parse next page from Link header
     */
    private function parseNextPageUrl($linkHeader)
    {
        if (empty($linkHeader)) {
            return null;
        }
        foreach (explode(',', $linkHeader[0]) as $part) {
            if (strpos($part, 'rel="next"') !== false) {
                if (preg_match('/<(.*)>/', $part, $matches)) {
                    return $matches[1];
                }
            }
        }
        return null;
    }

    /**
     * Parse currency symbol
     */
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
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'INR' => '₹',
            'JPY' => '¥',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'SGD' => 'S$',
            'CHF' => 'CHF',
        ];
        return $currencyMap[$currencyCode] ?? $currencyCode;
    }

    /**
     * Safe GET with retry logic
     */
    private function safeGet(string $url, array $options = [], $attempt = 0)
    {
        usleep(500000);
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
