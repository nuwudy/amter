<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\RedirectResponse;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        // Explicitly ensuring session is clean to prevent 419 errors
        session()->invalidate();
        session()->regenerateToken();

        // Force redirect to our custom login route
        return redirect()->route('login');
    }
}
