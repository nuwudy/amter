<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect()->route('public.library');
        }

        return response()
            ->view('auth.register')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'ദയവായി നിങ്ങളുടെ പേര് നൽകുക (Please enter your name).',
            'email.required' => 'ദയവായി സാധുവായ ഇമെയിൽ നൽകുക (Please enter your email).',
            'email.unique' => 'ഈ ഇമെയിൽ ഇതിനകം രജിസ്റ്റർ ചെയ്തിട്ടുണ്ട് (This email is already registered).',
            'password.required' => 'ദയവായി പാസ്‌വേഡ് നൽകുക (Please enter a password).',
            'password.min' => 'പാസ്‌വേഡിന് കുറഞ്ഞത് 6 അക്ഷരങ്ങൾ ഉണ്ടായിരിക്കണം (Password must be at least 6 characters).',
            'password.confirmed' => 'പാസ്‌വേഡ് സ്ഥിരീകരണം പൊരുത്തപ്പെടുന്നില്ല (Passwords do not match).',
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => strtolower(trim($validated['email'])),
                'password' => $validated['password'], // User model casts to hashed automatically
                'role' => 'student',
            ]);

            Log::info('Custom Auth: Registration successful', ['email' => $user->email]);

            Auth::login($user);
            $request->session()->regenerate();
            session()->flash('just_logged_in', true);

            // Redirect back to intended page (e.g. library or unit) or student dashboard
            return redirect()->intended(route('public.library'))
                ->with('success', 'സ്വാഗതം! നിങ്ങളുടെ ഫ്രീ അക്കൗണ്ട് വിജയകരമായി തയ്യാറായി. ഇപ്പോൾ കൂടുതൽ ക്ലാസ്സുകൾ ആസ്വദിക്കാം!');
        } catch (\Exception $e) {
            Log::error('Registration Error', ['error' => $e->getMessage()]);
            return back()->withInput()->withErrors(['email' => 'റജിസ്ട്രേഷൻ പരാജയപ്പെട്ടു. ദയവായി വീണ്ടും ശ്രമിക്കുക.']);
        }
    }
}
