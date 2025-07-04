<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Telegram\WebhookController;
//use App\Http\Controllers\Telegram\TelegramController;
// use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Laravel\Http\Middleware\ValidateWebhook;

Route::post('/telegram/webhook', WebhookController::class);
//Route::post('/bot/webhook', [TelegramController::class, 'webhook']);

Route::group(['middleware' => ValidateWebhook::class], function (): void {

    //Route::post('/{bot}/webhook', config('telegram.webhook.controller'))->name('telegram.bot.webhook');

    //    # Longpolling method (manual).
    //    Route::get('/{token}/updates/{bot?}', function ($bot = 'default') {
    //         # This method will fetch updates,
    //         # fire relevant events and,
    //         # confirm we've received the updates with Telegram.
    //
    //         $updates = Telegram::bot($bot)->listen();
    //
    //         # You can do something with the fetched array of update objects.
    //
    //         # NOTE: You won't be able to fetch updates if a webhook is set.
    //         # Remove webhook before using this method.
    //    })->name('telegram.bot.updates');
});
