<?php

namespace App\Filament\Student\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Support\Facades\Log;

class Login extends BaseLogin
{
    // The global LoginResponse singleton will now handle the redirect automatically.
    // UPDATE: The singleton binding is proving unreliable in this context.
    // We are forcing the usage of our custom response class here.
    protected function getLoginResponse(): LoginResponse
    {
        return new \App\Http\Responses\LoginResponse();
    }
}
