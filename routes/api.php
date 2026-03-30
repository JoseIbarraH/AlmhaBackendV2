<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Src\Admin\User\Infrastructure\Controllers\GetAuthenticatedUserController;

Route::middleware('auth:api')->get('/user', GetAuthenticatedUserController::class);


