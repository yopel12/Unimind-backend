<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test-register', function () {
    return response()->json(['msg' => 'GET works']);
});
