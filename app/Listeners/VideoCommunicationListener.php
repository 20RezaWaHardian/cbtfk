<?php

namespace App\Listeners;

use App\Events\IceCandidateSent;
use App\Events\VideoOfferSent;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;

class VideoCommunicationListener
{
    protected $pusher;

    public function __construct()
    {
        // Inisialisasi Pusher
        $this->pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true
            ]
        );
    }

    public function handleIceCandidateSent(IceCandidateSent $event)
    {
        // Kirim kandidat ICE ke admin melalui Pusher
        Log::info('ICE candidate received', [
            'candidate' => $event->candidate,
            'participant_id' => $event->participantId
        ]);

        $this->pusher->trigger('video-channel', 'ice-candidate', [
            'candidate' => $event->candidate,
            'participant_id' => $event->participantId
        ]);
    }

    public function handleVideoOfferSent(VideoOfferSent $event)
    {
        // Kirim tawaran video ke admin melalui Pusher
        Log::info('Video offer received', [
            'offer' => $event->offer,
            'participant_id' => $event->participantId
        ]);

        $this->pusher->trigger('video-channel', 'video-offer', [
            'offer' => $event->offer,
            'participant_id' => $event->participantId
        ]);
    }
}
