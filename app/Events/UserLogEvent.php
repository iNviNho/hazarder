<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Auth;

class UserLogEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $info;
    public $user_id;
    public $user_ticket_id;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($info, $userID, $userTicketID = null)
    {
        $this->info = $info;
        $this->user_id = $userID;
        $this->user_ticket_id = $userTicketID;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
