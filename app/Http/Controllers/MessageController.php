<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;

class MessageController extends Controller
{
    public function index(Request $request) {
        return Message::where('sender_id', $request->user()->id)
                      ->orWhere('receiver_id', $request->user()->id)
                      ->with('sender','receiver')
                      ->latest()
                      ->get();
    }

    public function store(Request $request) {
        $msg = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'content' => $request->content,
        ]);
        return response()->json($msg);
    }
}
