<?php

namespace App\Http\Middleware;

use Closure;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    protected $admins = ['185466160', '573204714', '18277740', '52090716'];

    public function handle($request, Closure $next)
    {
        if (in_array(session('id'), $this->admins))
        {
            return $next($request);
        }
        
        abort('404');
        
    }
}
