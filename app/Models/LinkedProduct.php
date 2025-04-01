<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkedProduct extends Model
{
    use HasFactory;

    protected $table = 'linked_products';

    protected $fillable = [
        'product_one_id',
        'product_two_id',
    ];

    // If you want relationships back to SelectedProduct
    public function productOne()
    {
        return $this->belongsTo(SelectedProduct::class, 'product_one_id');
    }

    public function productTwo()
    {
        return $this->belongsTo(SelectedProduct::class, 'product_two_id');
    }
}
