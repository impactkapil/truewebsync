<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'package_id',
        'card_number',
        'card_holder',
        'expiry_date',
        'cvv',
        'status',
        'stripe_id'
    ];

    /**
     * Get the customer that owns the package.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the package associated with the user package.
     */
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }
}
