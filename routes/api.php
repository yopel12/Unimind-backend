<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MessageController;
use Pusher\Pusher;


Route::get('/ping', function () {
    return response()->json(['status' => 'API is working!']);
});

// Auth routes
Route::post('/register', [AuthController::class, 'register']);


Route::post('/login', [AuthController::class, 'login']);

Route::get('/me', [AuthController::class, 'me']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/messages', [MessageController::class, 'index']);
Route::post('/messages', [MessageController::class, 'store']);


$waitingUser = null;

Route::post('/match', function (Request $request) {
    $userId = $request->input('userId');
    $filePath = storage_path('app/waiting_user.txt');

    $waitingUser = file_exists($filePath)
        ? trim(file_get_contents($filePath))
        : null;

    if (!$waitingUser) {
        file_put_contents($filePath, $userId);
        return response()->json(['message' => 'Waiting for another user...']);
    } else {
        unlink($filePath); // clear waiting user

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]
        );

        $pusher->trigger('unimind-channel', 'match-found', [
            'users' => [$userId, $waitingUser],
        ]);

        return response()->json([
            'message' => 'Match found!',
            'users' => [$userId, $waitingUser],
        ]);
    }
});
