<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Landing\Subscription\Infrastructure\Controllers\SubscribeController;
use Src\Landing\Subscription\Infrastructure\Controllers\ConfirmSubscriptionController;

Route::prefix('subscribe')->group(function () {
    Route::post('/', SubscribeController::class);
    Route::post('/confirm', ConfirmSubscriptionController::class);
});
