<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;

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

        $activeSessionId = Cache::get('active_system_session_id');
        $currentSessionId = session()->getId();

        if ($activeSessionId && $activeSessionId !== $currentSessionId) {
            session()->forget('system_unlocked');
            return redirect('/unlock')->withErrors(['password' => 'Your session was closed because another device logged in.']);
        }

        if ($activeSessionId === $currentSessionId) {
            Cache::put('active_system_session_id', $currentSessionId, now()->addMinutes(config('session.lifetime', 120)));
        }

        return $next($request);
    }
}
