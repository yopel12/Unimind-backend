<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use Pusher\Pusher;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        return Message::latest()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'matchId' => 'required',
            'content' => 'required|string',
            'sender_id' => 'required|integer',
            'receiver_id' => 'required|integer',
        ]);

        // Create and save message
        $msg = Message::create([
            'match_id' => $request->matchId,
            'sender_id' => $request->sender_id,
            'receiver_id' => $request->receiver_id,
            'content' => $request->content,
        ]);

        // Initialize Pusher
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
            ]
        );

        // Channel name matches frontend: chat-{matchId}
        $channelName = 'chat-' . $request->matchId;

        // Trigger message broadcast
        $pusher->trigger($channelName, 'message-sent', [
            'message' => [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'receiver_id' => $msg->receiver_id,
                'content' => $msg->content,
                'timestamp' => $msg->created_at,
                'isOwnMessage' => false,
            ],
        ]);

        return response()->json(['message' => $msg], 201);
    }
}
