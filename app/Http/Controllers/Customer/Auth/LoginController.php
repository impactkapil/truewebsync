<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest.guard:customer')->except('logout');
    }
    
    public function showLoginForm()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }
        return view('customer.auth.login');
    }

   

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::guard('customer')->attempt($credentials, $request->filled('remember'))) {
            $customer = Auth::guard('customer')->user();

            if (!$customer->hasVerifiedEmail()) {
                Auth::guard('customer')->logout();
                return back()->withErrors([
                    'email' => 'Please verify your email address before logging in.',
                ]);
            }

            $request->session()->regenerate();

            return redirect()->intended(route('customer.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}
