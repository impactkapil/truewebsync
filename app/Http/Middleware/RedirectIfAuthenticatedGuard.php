<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticatedGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  ...$guards
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Define redirection based on guard
                switch ($guard) {
                    case 'admin':
                        return redirect()->route('admin.dashboard');
                        break;
                    case 'customer':
                        return redirect()->route('customer.dashboard');
                        break;
                    default:
                        return redirect('/home');
                        break;
                }
            }
        }

        return $next($request);
    }
}
