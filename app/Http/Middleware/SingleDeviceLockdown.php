<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SingleDeviceLockdown
{
    public const COOKIE_NAME = 'amter_device_token';
    public const MAX_DEVICES = 2;

    /**
     * Get the list of active device tokens stored in the user's last_session_id column.
     */
    public static function getActiveTokens($user): array
    {
        if (!$user || empty($user->last_session_id)) {
            return [];
        }

        $parts = array_filter(array_map('trim', explode(',', (string) $user->last_session_id)));

        // Only keep persistent device tokens (prefixed with dt_), ignoring legacy PHP session IDs
        return array_values(array_filter($parts, fn (string $token) => str_starts_with($token, 'dt_')));
    }

    /**
     * Retrieve the current browser's device token or generate + queue a new 1-year cookie token.
     */
    public static function resolveOrCreateToken(?Request $request = null): string
    {
        $request = $request ?? request();

        $token = $request?->attributes->get(self::COOKIE_NAME)
            ?: $request?->cookie(self::COOKIE_NAME)
            ?: session(self::COOKIE_NAME);

        if (!$token || !is_string($token) || !str_starts_with($token, 'dt_')) {
            $token = 'dt_' . Str::random(29);
        }

        $request?->attributes->set(self::COOKIE_NAME, $token);
        session([self::COOKIE_NAME => $token]);
        Cookie::queue(cookie(self::COOKIE_NAME, $token, 60 * 24 * 365));

        return $token;
    }

    /**
     * Register the current browser/device for the user (allowing up to MAX_DEVICES active devices).
     */
    public static function registerDevice($user, ?Request $request = null): string
    {
        $token = self::resolveOrCreateToken($request);

        if (!$user) {
            return $token;
        }

        $tokens = self::getActiveTokens($user);
        $tokens = array_values(array_diff($tokens, [$token]));

        if (count($tokens) >= self::MAX_DEVICES) {
            $tokens = array_slice($tokens, -(self::MAX_DEVICES - 1));
        }

        $tokens[] = $token;
        $newValue = implode(',', $tokens);

        if ($user->last_session_id !== $newValue) {
            $user->last_session_id = $newValue;
            $user->saveQuietly();
        }

        return $token;
    }

    /**
     * Remove the current device token when a user explicitly logs out.
     */
    public static function unregisterCurrentDevice($user, ?Request $request = null): void
    {
        if (!$user) {
            return;
        }

        $request = $request ?? request();
        $token = $request?->attributes->get(self::COOKIE_NAME)
            ?: $request?->cookie(self::COOKIE_NAME)
            ?: session(self::COOKIE_NAME);

        if (!$token) {
            return;
        }

        $tokens = array_values(array_diff(self::getActiveTokens($user), [$token]));
        $user->last_session_id = !empty($tokens) ? implode(',', $tokens) : null;
        $user->saveQuietly();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Never lock out admin accounts
            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                return $next($request);
            }

            $currentToken = $request->attributes->get(self::COOKIE_NAME)
                ?: $request->cookie(self::COOKIE_NAME)
                ?: session(self::COOKIE_NAME);

            $activeTokens = self::getActiveTokens($user);

            // 1. Current device is already registered as one of the user's allowed devices
            if ($currentToken && in_array($currentToken, $activeTokens, true)) {
                session([self::COOKIE_NAME => $currentToken]);
                if (!$request->cookie(self::COOKIE_NAME)) {
                    Cookie::queue(cookie(self::COOKIE_NAME, $currentToken, 60 * 24 * 365));
                }
                return $next($request);
            }

            // 2. Fresh login OR user has fewer than 2 registered devices (smooth migration & 2nd device support)
            if (session('just_logged_in') || count($activeTokens) < self::MAX_DEVICES) {
                self::registerDevice($user, $request);
                return $next($request);
            }

            // 3. User already has 2 other active devices and this device is not one of them
            auth()->logout();

            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('login')->with('error', 'You have been logged out because your account is active on another device.');
        }

        return $next($request);
    }
}
