<?php

use Illuminate\Support\Facades\Route;
use Src\Admin\Analytics\Infrastructure\Controllers\GetAppPulseController;
use Src\Admin\Analytics\Infrastructure\Controllers\GetBehaviorController;
use Src\Admin\Analytics\Infrastructure\Controllers\GetDashboardKpisController;
use Src\Admin\Analytics\Infrastructure\Controllers\GetUserProfileController;
use Src\Admin\Analytics\Infrastructure\Controllers\GetValuableActionsController;

Route::prefix('analytics')
    /* ->middleware(['auth:api', 'role:super_admin|admin']) */
    ->group(function () {
        Route::get('/kpis', GetDashboardKpisController::class);
        Route::get('/pulse', GetAppPulseController::class);
        Route::get('/behavior', GetBehaviorController::class);
        Route::get('/profile', GetUserProfileController::class);
        Route::get('/actions', GetValuableActionsController::class);
    });
