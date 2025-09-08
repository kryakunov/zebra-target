<?php

namespace App\Http\Middleware;

use Closure;

class AuthVk
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (session('token')) {
            return $next($request);
        }
        
        return redirect()->route('guest');
    }
}
