<?php

use Src\Admin\Analytics\Infrastructure\Controllers\GetDashboardStatsController;

Route::prefix('analytics')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::get('/dashboard/stats', GetDashboardStatsController::class);
    });
