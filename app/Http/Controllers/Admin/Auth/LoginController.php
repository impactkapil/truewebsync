<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    public function __construct()
    {
        // Only guests can access login methods, except logout
        $this->middleware('guest.guard:admin')->except('logout');
    }

    // Show Admin Login Form
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    // Handle Admin Login
    public function login(Request $request)
    {
        // dd($request);
        // Validate input
        $this->validate($request, [
            'email'   => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Attempt to log in as admin
        if (Auth::guard('admin')->attempt(
            ['email' => $request->email, 'password' => $request->password],
            $request->remember
        )) {
            
            // Authentication passed
            // return redirect()->intended(route('admin.dashboard'));
            return redirect()->route('admin.dashboard');
        }

        // Authentication failed
        return redirect()->back()->withInput($request->only('email', 'remember'))->withErrors([
            'email' => 'Credentials do not match our records.',
        ]);
    }

    // Handle Admin Logout
    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect('/admin/login');
    }
}