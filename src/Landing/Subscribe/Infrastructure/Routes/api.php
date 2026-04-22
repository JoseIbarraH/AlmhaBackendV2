<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Landing\Subscription\Infrastructure\Controllers\ConfirmSubscriptionController;
use Src\Landing\Subscription\Infrastructure\Controllers\SubscribeController;

// Re-expose the existing Landing/Subscription controllers under /api/client/subscribe
// so the frontend doesn't need to change endpoints.
Route::prefix('subscribe')->group(function () {
    Route::post('/', SubscribeController::class);
    Route::post('/confirm', ConfirmSubscriptionController::class);
});
