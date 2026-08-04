<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\StudentOnboarded;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Send onboarding email to the new user
        // We might want to check if the user is a student role if roles exist, 
        // but for now, the requirement implies all new users are students/users of the wealth machine.
        
        Mail::to($user)->send(new StudentOnboarded($user));
    }
}
