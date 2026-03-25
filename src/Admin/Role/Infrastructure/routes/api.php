<?php

use Illuminate\Support\Facades\Route;
use Src\Admin\Role\Infrastructure\Controllers\CreateRoleController;
use Src\Admin\Role\Infrastructure\Controllers\AssignRoleController;
use Src\Admin\Role\Infrastructure\Controllers\GetRolesController;
use Src\Admin\Role\Infrastructure\Controllers\GetPermissionsController;

Route::prefix('roles')->group(function () {
    Route::get('/', GetRolesController::class);
    Route::post('/', CreateRoleController::class);
    Route::post('/assign', AssignRoleController::class);
});

Route::prefix('permissions')->group(function () {
    Route::get('/', GetPermissionsController::class);
});
