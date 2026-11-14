<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SingleDeviceLockdown;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PageController extends Controller
{
    public function about()
    {
        return view('pages.about');
    }

    public function terms()
    {
        return view('pages.terms');
    }

    public function privacy()
    {
        return view('pages.privacy');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function refund()
    {
        return view('pages.refund');
    }

    /**
     * GET /delete-account
     * Public account & data deletion page required by Google Play.
     */
    public function deleteAccount()
    {
        return view('pages.delete-account');
    }

    /**
     * POST /delete-account
     * Process permanent account and personal data deletion for logged-in or credential-verified users.
     */
    public function destroyAccount(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $request->validate([
                'password' => ['required', 'string'],
            ]);

            if (!Hash::check((string) $request->input('password'), $user->password)) {
                return back()->withErrors([
                    'password' => 'The password you entered does not match your account.',
                ])->withInput();
            }
        } else {
            $validated = $request->validate([
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ]);

            $user = User::where('email', strtolower(trim($validated['email'])))->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return back()->withErrors([
                    'email' => 'No matching account was found with those credentials.',
                ])->onlyInput('email');
            }
        }

        if ($user->isAdmin()) {
            return back()->withErrors([
                'email' => 'Administrator accounts cannot be deleted via the public self-service form.',
            ]);
        }

        if (Auth::check() && Auth::id() === $user->id) {
            SingleDeviceLockdown::unregisterCurrentDevice($user, $request);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        DB::transaction(function () use ($user) {
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }
            $user->completedUnits()->detach();
            $user->courses()->detach();
            $user->milestones()->delete();
            $user->delete();
        });

        return redirect()->route('delete.account')->with(
            'account_deleted',
            'Your Amter account and associated personal learning data have been permanently deleted.'
        );
    }
}
