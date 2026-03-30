<?php

use Illuminate\Support\Facades\Route;
use Src\Admin\Team\Infrastructure\Controllers\CreateTeamController;
use Src\Admin\Team\Infrastructure\Controllers\GetAllTeamsController;
use Src\Admin\Team\Infrastructure\Controllers\GetTeamController;
use Src\Admin\Team\Infrastructure\Controllers\UpdateTeamController;
use Src\Admin\Team\Infrastructure\Controllers\DeleteTeamController;

Route::prefix('teams')->middleware(['auth:api'])->group(function () {
    Route::post('/', CreateTeamController::class);
    Route::get('/', GetAllTeamsController::class);
    Route::get('/{id}', GetTeamController::class);
    Route::post('/{id}', UpdateTeamController::class);
    Route::delete('/{id}', DeleteTeamController::class);
});
