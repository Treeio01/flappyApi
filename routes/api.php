<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DiscordController;
use App\Http\Controllers\GiveawayController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\UserController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/health', fn() => ['ok' => true]);

// OAuth (если фронт просит прям ссылку — можно сделать /auth/discord/url)
Route::get('/auth/discord/url', [DiscordController::class, 'redirect']);
Route::get('/auth/discord/callback', [DiscordController::class, 'callback']);

// защищённые
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [UserController::class, 'me']);

    // публичные (для юзера)
    Route::get('/giveaways', [GiveawayController::class, 'index']);
    Route::post('/entries', [EntryController::class, 'store']);

    // админка
    Route::middleware('admin')->group(function () {
        Route::post('/giveaways', [GiveawayController::class, 'store']);
        Route::post('/giveaways/{id}', [GiveawayController::class, 'update']);
        Route::post('/giveaways/{id}/end', [GiveawayController::class, 'end']);
        Route::post('/giveaways/{id}/winner-toggle', [GiveawayController::class, 'toggleWinner']);
        Route::delete('/giveaways/{id}', [GiveawayController::class, 'destroy']);

        Route::get('/entries', [EntryController::class, 'index']);
        Route::patch('/entries/{id}/verify', [EntryController::class, 'verify']);
    });
});

