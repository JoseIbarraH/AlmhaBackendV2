<?php

use Illuminate\Support\Facades\Route;
use Src\Admin\User\Infrastructure\Controllers\CreateUserController;
use Src\Admin\User\Infrastructure\Controllers\GetUserController;
use Src\Admin\User\Infrastructure\Controllers\GetAllUsersController;
use Src\Admin\User\Infrastructure\Controllers\GetUserByCriteriaController;
use Src\Admin\User\Infrastructure\Controllers\UpdateUserController;
use Src\Admin\User\Infrastructure\Controllers\DeleteUserController;
use Src\Admin\User\Infrastructure\Controllers\GetAuthenticatedUserController;

Route::get('/user', GetAuthenticatedUserController::class)->middleware('auth:api');

Route::prefix('users')
    ->middleware(['auth:api'])
    ->group(function () {
    Route::get('/', GetAllUsersController::class)->middleware('permission:view_users');
    Route::post('/', CreateUserController::class)->middleware('permission:create_users');
    Route::get('/search', GetUserByCriteriaController::class)->middleware('permission:search_users');
    Route::get('/{id}', GetUserController::class)->middleware('permission:view_user_detail');
    Route::post('/{id}', UpdateUserController::class)->middleware('permission:edit_users');
    Route::delete('/{id}', DeleteUserController::class)->middleware('permission:delete_users');
});
