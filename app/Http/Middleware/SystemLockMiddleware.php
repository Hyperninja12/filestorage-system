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

        $activeSessions = Cache::get('active_system_sessions', []);
        $now = now()->timestamp;
        $activeSessions = array_filter($activeSessions, fn($expiry) => $expiry > $now);

        $currentSessionId = session()->getId();

        if (!isset($activeSessions[$currentSessionId])) {
            session()->forget('system_unlocked');
            return redirect('/unlock')->withErrors(['password' => 'Your session was closed because the device limit (2) was reached by newer logins.']);
        }

        $activeSessions[$currentSessionId] = now()->addMinutes(config('session.lifetime', 120))->timestamp;
        Cache::put('active_system_sessions', $activeSessions, now()->addMinutes(config('session.lifetime', 120)));

        return $next($request);
    }
}
