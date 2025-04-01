<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    /**
     * Handle email verification.
     *
     * @param  int  $id
     * @param  string  $hash
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify($id, $hash)
    {
        $customer = Customer::findOrFail($id);

        if (sha1($customer->email) !== $hash) {
            return redirect()->route('homepage')->with('error', 'Invalid verification link.');
        }

        if ($customer->hasVerifiedEmail()) {
            return redirect()->route('homepage')->with('info', 'Email already verified.');
        }

        $customer->markEmailAsVerified();

        // Auth::guard('customer')->login($customer);

        return redirect()->route('customer.login')->with('success', 'Your email has been verified. You can now log in.');
    }
}
