<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('user_id')) {
            return redirect()->route('login');
        }

        if (session('user_role') !== 'admin') {
            return redirect()->route('supply.dashboard')->with('error', 'Access denied. Admin privileges required.');
        }

        return $next($request);
    }
}
