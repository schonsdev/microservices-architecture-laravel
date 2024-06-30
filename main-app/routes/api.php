<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/send-message', [MessageController::class, 'sendMessage']);
Route::get('/message/{id}', [MessageController::class, 'getMessage']);
