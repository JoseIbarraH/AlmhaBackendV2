<?php

use Illuminate\Support\Facades\Route;
use Src\Admin\Auth\Infrastructure\LoginController;

Route::prefix('auth')->group(function () {
    Route::post('login', LoginController::class);
});
