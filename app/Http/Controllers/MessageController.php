<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use Pusher\Pusher;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        return Message::where('sender_id', $request->user()->id)
                      ->orWhere('receiver_id', $request->user()->id)
                      ->with('sender', 'receiver')
                      ->latest()
                      ->get();
    }

    public function store(Request $request)
    {
        $msg = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'content' => $request->content,
        ]);

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true
            ]
        );

        // Use consistent channel name per pair
        $channelName = 'chat-' . min($request->user()->id, $request->receiver_id) . '-' . max($request->user()->id, $request->receiver_id);

        $pusher->trigger($channelName, 'message-sent', [
            'message' => $msg
        ]);

        return response()->json($msg);
    }
}
