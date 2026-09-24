<?php

namespace App\Events;

use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class VideoStreamEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamData;
    public $participantId;

    public function __construct($streamData, $participantId)
    {
        $this->streamData = $streamData;
        $this->participantId = $participantId;
    }

    public function broadcastOn()
    {
        Log::info('Broadcasting stream-update event for participant: ' . $this->participantId);

        return new PrivateChannel('ujian-channel');
    }

    public function broadcastAs()
    {
        return 'stream-update';
    }

    public function broadcastWith()
    {
        // Menyediakan data yang akan dikirimkan ke klien
        return [
            'participantId' => $this->participantId,
            'streamData' => $this->streamData,
        ];
    }
}
