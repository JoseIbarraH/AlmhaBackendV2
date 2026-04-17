<?php

use Illuminate\Support\Facades\Route;
use Src\Admin\Settings\Infrastructure\Controllers\GetSettingsController;
use Src\Admin\Settings\Infrastructure\Controllers\SaveSettingsController;

Route::middleware('auth:api')->prefix('settings')->group(function () {
    Route::get('/', GetSettingsController::class);
    Route::post('/', SaveSettingsController::class);
});
