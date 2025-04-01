<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SelectedProduct extends Model
{
    use HasFactory;

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
        'location_ids' => 'array',
    ];
    
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function shopifyStore()
    {
        return $this->belongsTo(ShopifyStore::class, 'shopify_store_id');
    }

    public function linkedProducts()
    {
        return $this->belongsToMany(SelectedProduct::class, 'linked_products', 'product_one_id', 'product_two_id');
    }

}
