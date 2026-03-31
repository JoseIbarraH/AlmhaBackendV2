<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Admin\User\Infrastructure\Controllers\IsSystemInitializedController;
use Src\Admin\User\Infrastructure\Controllers\RegisterFirstAdminController;

Route::prefix('setup')->group(function () {
    Route::get('/instance-status', IsSystemInitializedController::class);
    Route::post('/instance-bootstrap', RegisterFirstAdminController::class);
});
