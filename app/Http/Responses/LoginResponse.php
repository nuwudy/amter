<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use Illuminate\Support\Facades\Log;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = auth()->user();
        
        // Ensure we are checking the user object directly
        $role = strtolower(trim($user->role ?? ''));

        Log::info('Login Redirect Check', ['email' => $user->email, 'role' => $role]);

        // If Admin, always send to the admin panel URL
        if ($role === 'admin') {
            return redirect()->to(Filament::getPanel('admin')->getUrl());
        }

        // Otherwise, send to the student dashboard or intended URL
        // If the user came from a specific page (like checkout), send them back there
        return redirect()->intended(Filament::getPanel('student')->getUrl());
    }
}
