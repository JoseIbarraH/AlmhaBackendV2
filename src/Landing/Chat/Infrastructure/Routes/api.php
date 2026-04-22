<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Landing\Chat\Infrastructure\Controllers\ChatController;

Route::middleware('throttle:chat')->group(function () {
    Route::post('/chat', ChatController::class);
});
