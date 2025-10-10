<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pusher\Pusher;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required',
            'content' => 'required|string',
        ]);

        // The same data structure your ChatInterface expects
        $message = [
            'id' => uniqid(),
            'content' => $request->input('content'),
            'timestamp' => now()->toISOString(),
            'isOwnMessage' => false,
        ];

        // Match the frontend’s channel naming scheme
        $receiverId = $request->input('receiver_id');
        $channel = 'chat-' . $receiverId;

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
            ]
        );

        // 👇 Must match what ChatInterface listens for
        $pusher->trigger($channel, 'message-sent', ['message' => $message]);

        return response()->json(['status' => 'sent']);
    }
}
