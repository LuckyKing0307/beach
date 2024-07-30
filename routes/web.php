<?php

use App\Http\Controllers\BotController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
//Route::get('/webhook', [BotController::class, 'updates'])->name('bot.get.update');
