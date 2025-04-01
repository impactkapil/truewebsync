<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopifyProduct extends Model
{
    protected $table = 'shopify_products';

    protected $fillable = [
        'customer_id',
        'shopify_store_id',
        'product_id',
        'product_title',
        'variant_name',
        'variant_id',
        'variant_sku',
        'variant_price',
        'variant_inventory',
        'location_ids',
        'currency_symbol',
        'variant_image',
        'brand',
        'tags',
        'product_type',
        'barcode',
    ];

    protected $casts = [
        'variant_inventory' => 'array',
        'location_ids'      => 'array',
    ];
}
