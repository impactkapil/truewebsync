<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // Import
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Billable;

class Customer extends Authenticatable implements MustVerifyEmail // Implement the interface
{
    use HasFactory, Notifiable, SoftDeletes, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'status',
        'stripe_id',
        'pm_type',
        'pm_last_four',
    ];

    /**
     * The attributes that should be hidden for arrays (e.g., JSON responses).
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'status' => 'boolean',
     
    ];

    /**
     * Automatically hash the password when setting it.
     *
     * @param string $password
     * @return void
     */
    public function setPasswordAttribute($password)
    {
        if (\Illuminate\Support\Facades\Hash::needsRehash($password)) {
            $this->attributes['password'] = bcrypt($password);
        } else {
            $this->attributes['password'] = $password;
        }
    }
    public function userPackages()
    {
        return $this->hasMany(UserPackage::class, 'customer_id');
    }

    public function activePackage()
    {
        return $this->hasOne(UserPackage::class, 'customer_id')->where('status', 1)->with('package');
    }

    public function subscriptions()
    {
        // Default morphMany uses `owner_id` and `owner_type`
        // So it references Subscription::owner()->morphTo()
        return $this->morphMany(\App\Models\Subscription::class, 'owner');
    }
    


}
