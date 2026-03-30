<?php

use Illuminate\Support\Facades\Route;
use Src\Admin\Role\Infrastructure\Controllers\AssignRoleController;
use Src\Admin\Role\Infrastructure\Controllers\GetRolesController;
use Src\Admin\Role\Infrastructure\Controllers\GetPermissionsController;

Route::prefix('roles')->middleware(['auth:api'])->group(function () {
    Route::get('/', GetRolesController::class)->middleware('permission:view_roles');
    Route::post('/assign', AssignRoleController::class)->middleware('permission:assign_roles');
});

Route::prefix('permissions')->middleware(['auth:api'])->group(function () {
    Route::get('/', GetPermissionsController::class)->middleware('permission:view_permissions');
});
