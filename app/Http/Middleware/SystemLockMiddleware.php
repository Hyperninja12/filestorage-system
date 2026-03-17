<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * I-lock ang tibuok system: kung wala’y system_unlocked sa session, redirect sa /unlock.
 */
class SystemLockMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session()->has('system_unlocked')) {
            return redirect('/unlock');
        }

        return $next($request);
    }
}
