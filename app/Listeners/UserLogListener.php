<?php

namespace App\Listeners;

use App\Events\UserLogEvent;
use App\UserLog;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserLogListener
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
     * @param  UserLogEvent  $event
     * @return void
     */
    public function handle(UserLogEvent $event)
    {
        $userLog = new UserLog();
        $userLog->user_id = $event->user_id;
        $userLog->user_ticket_id = $event->user_ticket_id;
        $userLog->info = $event->info;
        $userLog->save();
    }
}
