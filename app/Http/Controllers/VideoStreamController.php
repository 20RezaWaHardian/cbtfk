<?php

namespace App\Http\Controllers;

use Pusher\Pusher;
use Illuminate\Http\Request;
use App\Events\VideoOfferSent;
use App\Events\IceCandidateSent;
use App\Events\VideoStreamEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

class VideoStreamController extends Controller
{

    // protected $pusher;

    // public function __construct()
    // {
    //     $this->pusher = new Pusher(
    //         env('PUSHER_APP_KEY'),
    //         env('PUSHER_APP_SECRET'),
    //         env('PUSHER_APP_ID'),
    //         [
    //             'cluster' => env('PUSHER_APP_CLUSTER'),
    //             'useTLS' => true,
    //         ]
    //     );
    // }

    // public function sendVideoOffer(Request $request)
    // {
    //     $participantId = $request->participant_id;
    //     $offer = $request->offer;

    //     // Kirim tawaran video ke peserta lain
    //     $this->pusher->trigger('video-channel', 'App\\Events\\VideoOfferSent', [
    //         'offer' => $offer,
    //         'participant_id' => $participantId
    //     ]);

    //     return response()->json(['status' => 'Offer sent']);
    // }

    // public function sendIceCandidate(Request $request)
    // {
    //     $participantId = $request->participant_id;
    //     $candidate = $request->candidate;

    //     // Kirim ICE candidate ke peserta lain
    //     $this->pusher->trigger('video-channel', 'App\\Events\\IceCandidateSent', [
    //         'candidate' => $candidate,
    //         'participant_id' => $participantId
    //     ]);
    //     return response()->json(['status' => 'ICE candidate sent']);
    // }

    public function startStream(Request $request)
    {
        $participantId = $request->input('participantId');
        $streamData = $request->input('streamData');

        Log::info("Menerima stream dari peserta: ", [
            'participantId' => $participantId,
            'streamData' => $streamData,
        ]);
        broadcast(new VideoStreamEvent($streamData, $participantId))->toOthers();
        Log::info('test: ' . $participantId);
    }

    public function authenticate(Request $request)
    {
        // Pastikan pengguna sudah login
        if (Auth::check()) {
            return Broadcast::auth($request);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }
}
