<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Pusher\Pusher;

class MatchingController extends Controller
{
    public function startMatching(Request $request)
    {
        try {
            $userId = $request->input('user_id', rand(1000, 9999)); // fallback for testing

            $waitingUser = Cache::pull('waiting_user');

            if ($waitingUser) {
                // Found another user waiting
                $pusher = new Pusher(
                    env('PUSHER_APP_KEY'),
                    env('PUSHER_APP_SECRET'),
                    env('PUSHER_APP_ID'),
                    [
                        'cluster' => env('PUSHER_APP_CLUSTER'),
                        'useTLS' => true,
                    ]
                );

                $matchData = [
                    'user1' => $waitingUser,
                    'user2' => $userId,
                ];

                // Trigger event
                $pusher->trigger('matching-channel', 'match-found', $matchData);

                return response()->json(['status' => 'matched', 'data' => $matchData]);
            } else {
                // No waiting user — store current user
                Cache::put('waiting_user', $userId, 60);
                return response()->json(['status' => 'waiting']);
            }

        } catch (\Exception $e) {
            // Catch and show what went wrong
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
