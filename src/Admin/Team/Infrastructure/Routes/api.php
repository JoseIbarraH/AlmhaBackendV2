<?php

use Illuminate\Support\Facades\Route;
use Src\Admin\Team\Infrastructure\Controllers\CreateTeamController;
use Src\Admin\Team\Infrastructure\Controllers\GetAllTeamsController;
use Src\Admin\Team\Infrastructure\Controllers\GetTeamController;
use Src\Admin\Team\Infrastructure\Controllers\UpdateTeamController;
use Src\Admin\Team\Infrastructure\Controllers\DeleteTeamController;

Route::prefix('teams')->middleware(['auth:api'])->group(function () {
    Route::post('/', CreateTeamController::class)->middleware('permission:create_teams');
    Route::get('/', GetAllTeamsController::class)->middleware('permission:view_teams');
    Route::get('/{id}', GetTeamController::class)->middleware('permission:view_team_detail');
    Route::post('/{id}', UpdateTeamController::class)->middleware('permission:edit_teams');
    Route::delete('/{id}', DeleteTeamController::class)->middleware('permission:delete_teams');
});
