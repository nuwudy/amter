<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function show()
    {
        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            session()->flash('just_logged_in', true);

            $user = Auth::user();
            $role = strtolower(trim($user->role ?? ''));

            Log::info('Custom Auth: Login successful', ['email' => $user->email, 'role' => $role]);

            if ($role === 'admin') {
                return redirect()->to(Filament::getPanel('admin')->getUrl());
            }

            Log::info('Redirecting to Student Dashboard', ['route' => route('filament.student.pages.dashboard')]);
            return redirect()->route('filament.student.pages.dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
}
