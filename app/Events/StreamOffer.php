<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel; // Gunakan ini jika ingin private
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StreamOffer implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $offer;
    public $participant_id;

    /**
     * Create a new event instance.
     *
     * @param array $offer
     * @param string $participant_id
     */
    public function __construct($offer, $participant_id)
    {
        $this->offer = $offer;
        $this->participant_id = $participant_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        // Jika ingin channel publik
        return new Channel('video-stream');

        // Jika ingin menggunakan private channel (perlu autentikasi)
        // return new PrivateChannel('video-stream');
    }

    /**
     * Get the event name that will be broadcast.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'stream-offer'; // Nama event di frontend
    }

    /**
     * Data yang akan dikirim ke frontend
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'offer' => $this->offer,
            'participant_id' => $this->participant_id,
        ];
    }
}
