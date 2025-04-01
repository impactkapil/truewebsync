<?php

// app/Models/OrderItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'shopify_product_id',
        'shopify_variant_id',
        'quantity',
        'price',
        'location_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function selectedProduct()
    {
        return $this->hasOne(SelectedProduct::class, 'variant_id', 'shopify_variant_id');
    }
}
