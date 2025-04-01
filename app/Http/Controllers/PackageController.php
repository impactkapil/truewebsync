<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserPackage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth:customer');
    }

    /**
     * Handle the purchase of a package.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function purchase(Request $request)
    {
        $rules = [
            'package_id'   => 'required|exists:packages,id',
            'card_holder'  => 'required|string|max:255',
            'card_number'  => 'required|digits:16',
            'expiry_date'  => ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
            'cvv'          => 'required|digits:3',
        ];

        
        $messages = [
            'package_id.required'  => 'Please select a package to purchase.',
            'package_id.exists'    => 'The selected package does not exist.',
            'card_holder.required' => 'Card holder name is required.',
            'card_number.required' => 'Card number is required.',
            'card_number.digits'   => 'Card number must be exactly 16 digits.',
            'expiry_date.required' => 'Expiry date is required.',
            'expiry_date.regex'    => 'Expiry date must be in MM/YY format.',
            'cvv.required'         => 'CVV is required.',
            'cvv.digits'           => 'CVV must be exactly 3 digits.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }

        $data = $validator->validated();

        DB::beginTransaction();

        $customer = Auth::guard('customer')->user();

        UserPackage::where('customer_id', $customer->id)
                   ->where('status', 1)
                   ->update(['status' => 0]);

        UserPackage::create([
            'customer_id'   => $customer->id,
            'package_id'    => $data['package_id'],
            'card_number'   => $data['card_number'],    
            'card_holder'   => $data['card_holder'],
            'expiry_date'   => $data['expiry_date'],
            'cvv'           => $data['cvv'],            
            'status'        => 1,                       
        ]);

        DB::commit();

        return redirect()->route('packages')
                         ->with('success', 'Package purchased successfully!');
    }
}
