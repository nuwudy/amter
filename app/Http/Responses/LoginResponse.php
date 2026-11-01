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

        $role = strtolower(trim($user->role ?? ''));

        Log::info('Login Redirect Check', ['email' => $user->email, 'role' => $role]);

        if ($role === 'admin') {
            return redirect()->to(Filament::getPanel('admin')->getUrl());
        }

        return redirect()->intended(Filament::getPanel('student')->getUrl());
    }
}
