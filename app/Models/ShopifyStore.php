<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifyStore extends Model
{
    use HasFactory;

    // Define the table name if different from the default (lowercase of model name)
    protected $table = 'shopify_stores';

    // Define fillable attributes to allow mass assignment
    protected $fillable = [
        'customer_id',
        'store_name',
        'access_token',
        'total_products',
        'imported_products',
        'shopify_domain',
        'status',
        'is_master',
        'webhooks_secret_key'
    ];

    // Define the relationship to the Customer model
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function selectedProducts()
    {
        return $this->hasMany(SelectedProduct::class, 'shopify_store_id');
    }
}
