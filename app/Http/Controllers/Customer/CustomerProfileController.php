<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerProfileController extends Controller
{
    /**
     * Show the Customer Profile.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        // Retrieve the authenticated Customer
        $customer = Auth::guard('customer')->user();

        return view('customer.profile', compact('customer'));
    }
}
