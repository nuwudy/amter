<?php

namespace App\Filament\Student\Pages\Auth;

use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;

class Register extends BaseRegister
{
    public function mount(): void
    {
        \Illuminate\Support\Facades\Log::info('Custom Register Page: MOUNTED.');
        parent::mount();
    }

    public function register(): ?RegistrationResponse
    {
        \Illuminate\Support\Facades\Log::info('Custom Register Page: register() method CALLED.');
        
        try {
            $response = parent::register();
            \Illuminate\Support\Facades\Log::info('Custom Register Page: parent::register() finished.', ['response_type' => get_class($response)]);
            return $response;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Custom Register Page: Exception in register()', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    // Override the response method ensuring we use our custom response
    public function getRegistrationResponse(): RegistrationResponse
    {
        \Illuminate\Support\Facades\Log::info('Custom Register Page: getRegistrationResponse call.');
        return app(RegistrationResponse::class);
    }
}
