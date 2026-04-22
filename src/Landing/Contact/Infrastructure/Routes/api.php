<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Landing\Contact\Infrastructure\Controllers\ContactController;

Route::middleware('throttle:contact')->group(function () {
    Route::post('/contact', ContactController::class);
});
