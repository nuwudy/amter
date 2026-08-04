<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admins pass free
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Check subscription
        if (!$user->subscription_expires_at || $user->subscription_expires_at->isPast()) {
            return redirect()->route('pricing')->with('warning', 'Your subscription has expired. Please renew to continue.');
        }

        return $next($request);
    }
}
