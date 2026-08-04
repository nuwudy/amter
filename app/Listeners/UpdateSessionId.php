<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateSessionId
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        // Flash a flag so middleware knows this is a fresh login
        // and can sync the new session ID after regeneration.
        session()->flash('just_logged_in', true);
        
        $user->last_session_id = session()->getId();
        $user->save();
    }
}
