<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\RegistrationResponse as RegistrationResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use Filament\Facades\Filament;

class RegisterResponse implements RegistrationResponseContract
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        \Illuminate\Support\Facades\Log::info('RegisterResponse Hit. Force redirecting to student dashboard.');
        
        // Explicitly redirect to the student dashboard route
        return redirect()->route('filament.student.pages.dashboard');
    }
}
