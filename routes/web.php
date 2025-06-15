<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;

Route::get('/', function () {
    return view('welcome');
});
Route::post('/bot/webhook', [TelegramController::class, 'webhook']);
