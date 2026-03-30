<?php

use Illuminate\Support\Facades\Route;
use Src\Admin\User\Infrastructure\Controllers\CreateUserController;
use Src\Admin\User\Infrastructure\Controllers\GetUserController;
use Src\Admin\User\Infrastructure\Controllers\GetAllUsersController;
use Src\Admin\User\Infrastructure\Controllers\GetUserByCriteriaController;
use Src\Admin\User\Infrastructure\Controllers\UpdateUserController;
use Src\Admin\User\Infrastructure\Controllers\DeleteUserController;

Route::prefix('users')
    ->middleware(['auth:api'])
    ->group(function () {
    Route::get('/', GetAllUsersController::class)->middleware('permission:listar usuarios');
    Route::post('/', CreateUserController::class)->middleware('permission:crear usuarios');
    Route::get('/search', GetUserByCriteriaController::class)->middleware('permission:buscar usuario');
    Route::get('/{id}', GetUserController::class)->middleware('permission:buscar usuario');
    Route::post('/{id}', UpdateUserController::class)->middleware('permission:editar usuarios');
    Route::delete('/{id}', DeleteUserController::class)->middleware('permission:eliminar usuarios');
});
