<?php

namespace App\Models;

use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends CashierSubscription
{
    protected $stripeExpand = ['items'];

    /**
     * Boot method for the model.
     * We'll set `customer_id` whenever a new subscription is created.
     */
    protected static function booted()
    {
        static::creating(function ($subscription) {
            // If `customer_id` is required but wasn't set by other logic,
            // auto-fill it with the same value as `owner_id`.
            if (is_null($subscription->customer_id)) {
                $subscription->customer_id = $subscription->owner_id;
            }
        });
    }

    // By NOT overriding owner(), we use the default:
    //   return $this->morphTo();
    // which relies on `owner_id` + `owner_type`.
}
