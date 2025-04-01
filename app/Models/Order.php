<?php

// app/Models/Order.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'shopify_store_id',
        'shopify_order_id',
        'location_id',
        'order_number',
        'order_name',
        'email',
        'total_price',
        'ordered_at',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
