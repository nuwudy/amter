<?php

namespace App\Listeners;

use App\Http\Middleware\SingleDeviceLockdown;
use Illuminate\Auth\Events\Login;

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
        session()->flash('just_logged_in', true);

        SingleDeviceLockdown::registerDevice($user, request());
    }
}
