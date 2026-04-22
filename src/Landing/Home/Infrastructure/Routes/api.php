<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Landing\Home\Infrastructure\Controllers\GetHomeDataController;

Route::get('/home', GetHomeDataController::class);
