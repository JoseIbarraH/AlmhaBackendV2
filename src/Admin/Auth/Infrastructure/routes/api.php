<?php

use Illuminate\Support\Facades\Route;
use Src\Admin\Auth\Infrastructure\LoginController;
use Src\Admin\Auth\Infrastructure\RefreshTokenController;

Route::prefix('auth')->group(function () {
    Route::post('login', LoginController::class);
    Route::post('refresh', RefreshTokenController::class)->middleware('auth:api');
});
