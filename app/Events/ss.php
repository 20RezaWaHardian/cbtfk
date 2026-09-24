<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IceCandidate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $candidate;
    public $participant_id;

    /**
     * Create a new event instance.
     *
     * @param array $candidate
     * @param string $participant_id
     */
    public function __construct($candidate, $participant_id)
    {
        $this->candidate = $candidate;
        $this->participant_id = $participant_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('video-stream');
    }

    public function broadcastAs()
    {
        return 'ice-candidate';
    }
}
