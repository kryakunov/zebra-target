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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Требуется авторизация'], 401);
        }

        if ($request->isMethod('get')) {
            return redirect()->route('guest')
                ->with('error', 'Войдите через ВК, чтобы открыть этот раздел.');
        }

        return redirect()->back()
            ->with('error', 'Войдите через ВК, чтобы выполнить это действие.');
    }
}
