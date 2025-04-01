<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'package_name',
        'number_of_shops',
        'number_of_products',
        'orders',
        'manage_customers',
        'price',
        'locations',
        'status',
    ];
    public function userPackages()
    {
        return $this->hasMany(UserPackage::class, 'package_id');
    }
}
