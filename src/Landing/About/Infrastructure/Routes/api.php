<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Landing\About\Infrastructure\Controllers\GetAboutController;

Route::get('/about', GetAboutController::class);
