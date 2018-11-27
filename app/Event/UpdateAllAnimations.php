<?php

namespace App\Event;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UpdateAllAnimations
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $type;
    public $update_list = [];

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($type, array $update_list)
    {
        $this->type = $type;
        $this->update_list = $update_list;
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
