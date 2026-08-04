<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SingleDeviceLockdown
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            $currentSession = session()->getId();

            // CHECK FOR FRESH LOGIN (Grace Period)
            if (session('just_logged_in')) {
                if ($user->last_session_id !== $currentSession) {
                    $user->last_session_id = $currentSession;
                    $user->saveQuietly();
                }
                return $next($request);
            }

            if ($user->last_session_id && $user->last_session_id !== $currentSession) {
                auth()->logout();
                
                // Invalidate the session
                session()->invalidate();
                session()->regenerateToken();

                return redirect()->route('login')->with('error', 'You have been logged out because your account is active on another device.');
            }
        }

        return $next($request);
    }
}
