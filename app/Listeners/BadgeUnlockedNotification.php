<?php

namespace App\Listeners;

use App\Events\BadgeUnlocked;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class BadgeUnlockedNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  BadgeUnlocked  $event
     * @return void
     */
    public function handle(BadgeUnlocked $event)
    {
        //
    }
}
