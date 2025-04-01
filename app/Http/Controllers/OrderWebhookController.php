<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShopifyStore;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SelectedProduct;
use App\Models\LinkedProduct;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use GuzzleHttp\Client; // For Shopify API calls

class OrderWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $webhookSetting = Setting::where('feature_name', 'orders')->first();
        if (!$webhookSetting || !$webhookSetting->is_enabled) {
            Log::info('Webhook processing for orders is disabled in settings.');
            return response('Webhook processing disabled', 200);
        }
        
        Log::info('--- Incoming Shopify Webhook: Orders ---');

        // 1. Identify the shop from the request header
        $shopDomain = $request->header('X-Shopify-Shop-Domain');
        Log::info('Step 1: Detected shop domain', ['shopDomain' => $shopDomain]);

        // 2. Fetch the corresponding store record
        $store = ShopifyStore::where('shopify_domain', $shopDomain)->first();
        if (!$store) {
            Log::error('Step 2: Unknown shop domain', ['shopDomain' => $shopDomain]);
            return response('Unknown shop domain', 404);
        }
        Log::info('Step 2: Found store record', ['store_id' => $store->id]);

        // 3. Get the store’s webhook secret from DB
        $webhookSecret = $store->webhooks_secret_key;
        Log::info('Step 3: Retrieved webhook secret from DB', ['secret_key_length' => strlen($webhookSecret)]);

        // 4. Verify HMAC
        $hmacHeader = $request->header('X-Shopify-Hmac-Sha256');
        $calculatedHmac = base64_encode(
            hash_hmac('sha256', $request->getContent(), $webhookSecret, true)
        );
        Log::info('Step 4: Calculated HMAC', [
            'received_hmac'   => $hmacHeader,
            'calculated_hmac' => $calculatedHmac
        ]);
        if (!hash_equals($hmacHeader, $calculatedHmac)) {
            Log::error('Step 4: Invalid webhook signature');
            return response('Invalid webhook signature', 401);
        }
        Log::info('Step 4: HMAC verification success');

        // 5. Parse the incoming JSON
        $orderData = $request->all();
        Log::info('Step 5: Parsed webhook JSON', ['orderData' => $orderData]);

        // Top-level location_id for the entire order
        $orderLocationId = $orderData['location_id'] ?? null;

        // Build a map of line_item_id => location_id from fulfillments
        $lineItemLocationMap = [];
        if (!empty($orderData['fulfillments']) && is_array($orderData['fulfillments'])) {
            foreach ($orderData['fulfillments'] as $fulfillment) {
                $fulfillmentLocationId = $fulfillment['location_id'] ?? null;
                if (!empty($fulfillment['line_items']) && is_array($fulfillment['line_items'])) {
                    foreach ($fulfillment['line_items'] as $fulfilledItem) {
                        $fulfilledItemId = $fulfilledItem['id'];
                        $lineItemLocationMap[$fulfilledItemId] = $fulfillmentLocationId;
                    }
                }
            }
        }

        // 6. Store or update the order
        $order = Order::updateOrCreate(
            ['shopify_order_id' => $orderData['id']],
            [
                'order_number'     => $orderData['order_number'] ?? null,
                'order_name'       => $orderData['name'] ?? null,
                'email'            => $orderData['email'] ?? null,
                'total_price'      => $orderData['total_price'] ?? 0,
                'ordered_at'       => isset($orderData['created_at'])
                                        ? Carbon::parse($orderData['created_at'])
                                        : null,
                'shopify_store_id' => $store->id,
                'location_id'      => $orderLocationId,
            ]
        );
        Log::info('Step 6: Order upserted', ['order_id' => $order->id]);

        // 7. Clear existing line items if updating
        $order->orderItems()->delete();
        Log::info('Step 7: Existing line items cleared');

        // 8. Insert line items and update local inventory
        if (!empty($orderData['line_items']) && is_array($orderData['line_items'])) {
            foreach ($orderData['line_items'] as $item) {
                $lineItemId = $item['id'];
                $perLineItemLocation = $lineItemLocationMap[$lineItemId] ?? $orderLocationId;

                // Create a new OrderItem record
                $createdItem = OrderItem::create([
                    'order_id'           => $order->id,
                    'shopify_product_id' => $item['product_id'] ?? null,
                    'shopify_variant_id' => $item['variant_id'] ?? null,
                    'quantity'           => $item['quantity'],
                    'price'              => $item['price'] ?? 0,
                    'location_id'        => $perLineItemLocation,
                ]);
                Log::info('Step 8: Created line item', [
                    'item_id'       => $createdItem->id,
                    'lineItemId'    => $lineItemId,
                    'location_id'   => $perLineItemLocation,
                    'quantity'      => $createdItem->quantity,
                    'variant_id'    => $createdItem->shopify_variant_id,
                ]);

                // ---- Update local inventory for main product ----
                if (!empty($createdItem->shopify_variant_id) && !empty($perLineItemLocation)) {
                    $selectedProduct = SelectedProduct::where('variant_id', $createdItem->shopify_variant_id)->first();
                    if ($selectedProduct) {
                        $inventoryData = json_decode($selectedProduct->variant_inventory, true);
                        if (!is_array($inventoryData)) {
                            $inventoryData = [];
                        }
                        if (isset($inventoryData[$perLineItemLocation])) {
                            // Subtract the ordered quantity without capping negative values
                            $inventoryData[$perLineItemLocation] -= $createdItem->quantity;
                        }
                        $selectedProduct->variant_inventory = json_encode($inventoryData);
                        $selectedProduct->save();
                        Log::info('Step 8b: Updated local inventory', [
                            'selected_product_id' => $selectedProduct->id,
                            'variant_id'          => $createdItem->shopify_variant_id,
                            'location_id'         => $perLineItemLocation,
                            'new_inventory_data'  => $inventoryData,
                        ]);

                        // ---- Update linked product inventory (ignoring location_id) ----
                        $linkedRecord = LinkedProduct::where('product_one_id', $selectedProduct->id)
                            ->orWhere('product_two_id', $selectedProduct->id)
                            ->first();
                        if ($linkedRecord) {
                            if ($linkedRecord->product_one_id == $selectedProduct->id) {
                                $otherProductId = $linkedRecord->product_two_id;
                            } else {
                                $otherProductId = $linkedRecord->product_one_id;
                            }
                            $otherSelectedProduct = SelectedProduct::find($otherProductId);
                            if ($otherSelectedProduct) {
                                $otherInventoryData = json_decode($otherSelectedProduct->variant_inventory, true);
                                if (!is_array($otherInventoryData)) {
                                    $otherInventoryData = [];
                                }
                                // Subtract the same quantity from every location key without capping at 0
                                foreach ($otherInventoryData as $locId => $qty) {
                                    $newQty = $qty - $createdItem->quantity;
                                    $otherInventoryData[$locId] = $newQty;
                                }
                                $otherSelectedProduct->variant_inventory = json_encode($otherInventoryData);
                                $otherSelectedProduct->save();
                                Log::info('Step 8c: Also updated linked product inventory', [
                                    'linked_product_id'   => $otherSelectedProduct->id,
                                    'quantity_subtracted' => $createdItem->quantity,
                                    'updated_inventory'   => $otherInventoryData,
                                ]);

                                /**
                                 * --- NEW: Push the linked product's updated inventory to Shopify ---
                                 * Instead of relying on a stored inventory_item_id, we now use the linked product's variant_id.
                                 * We use the ShopifyStore's domain and access token to fetch the inventory_item ID via GraphQL.
                                 * For the child product, we pick its first location key (if multiple exist).
                                 */
                                if (!empty($store->access_token) && !empty($store->shopify_domain)) {
                                    if ($otherSelectedProduct->shopifyStore) {
                                        $childShopifyStore = $otherSelectedProduct->shopifyStore;
                                        $childVariantId = $otherSelectedProduct->variant_id;
                                        $childInv = json_decode($otherSelectedProduct->variant_inventory, true);
                                        if (!is_array($childInv)) {
                                            $childInv = [];
                                        }
                                        $childLocationId = array_key_first($childInv);
                                        if ($childLocationId) {
                                            $childCurrentLocal = (int)($childInv[$childLocationId] ?? 0);
                                            try {
                                                Log::info('Step 8d: Attempting to update Shopify for linked product', [
                                                    'childVariantId'    => $childVariantId,
                                                    'childLocationId'   => $childLocationId,
                                                    'childCurrentLocal' => $childCurrentLocal,
                                                    'childNewQty'       => $childInv[$childLocationId]
                                                ]);
                                                $this->updateShopifyVariantInventory(
                                                    $childShopifyStore,
                                                    $childVariantId,
                                                    'set',
                                                    $childInv[$childLocationId],
                                                    $childCurrentLocal,
                                                    $childLocationId
                                                );
                                            } catch (\Exception $e) {
                                                Log::error('Error updating Shopify inventory for linked child product', [
                                                    'error' => $e->getMessage(),
                                                ]);
                                            }
                                        } else {
                                            Log::info('Step 8d: No child location found in linked product inventory');
                                        }
                                    } else {
                                        Log::info('Step 8d: Linked product has no ShopifyStore associated');
                                    }
                                } else {
                                    Log::info('Step 8d: Store missing access_token or domain for Shopify API call');
                                }
                            } else {
                                Log::info('Step 8c: Linked product record not found for ID', [
                                    'otherProductId' => $otherProductId
                                ]);
                            }
                        }
                    } else {
                        Log::info('Step 8b: No matching selected_product found for variant_id', [
                            'variant_id' => $createdItem->shopify_variant_id
                        ]);
                    }
                } else {
                    Log::info('Step 8b: Skipped inventory update (missing variant_id or location_id)', [
                        'variant_id'  => $createdItem->shopify_variant_id,
                        'location_id' => $perLineItemLocation,
                    ]);
                }
            }
        } else {
            Log::info('Step 8: No line items found in payload');
        }

        // Step 9: Return a success response
        Log::info('Step 9: Webhook processed successfully');
        return response('Webhook received', 200);
    }

    /**
     * Private function to update Shopify variant inventory using GraphQL.
     *
     * @param  \App\Models\ShopifyStore $shopifyStore
     * @param  string $variantId
     * @param  string $operation ('set' or 'adjust')
     * @param  int    $qty
     * @param  int    $currentLocalInv
     * @param  string $locationId
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

        // Ensure locationId is in the required GID format
        if (!str_starts_with($locationId, 'gid://shopify/Location/')) {
            $locationId = "gid://shopify/Location/{$locationId}";
        }
        Log::info("Converted locationId to GID if needed", ['locationId' => $locationId]);

        $variantGid  = "gid://shopify/ProductVariant/{$variantId}";
        $shopDomain  = $shopifyStore->shopify_domain;
        $accessToken = $shopifyStore->access_token;
        $client = new Client();
        $url = "https://{$shopDomain}/admin/api/2023-10/graphql.json";

        // 1) Fetch the InventoryItem ID for this variant using GraphQL
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

        // 2) Build the GraphQL mutation to set or adjust inventory
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
}
