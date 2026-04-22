<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Landing\Subscription\Infrastructure\Controllers\ConfirmSubscriptionController;
use Src\Landing\Subscription\Infrastructure\Controllers\SubscribeController;

/**
 * Subscription endpoints. Loaded from bootstrap/app.php inside the `api/client`
 * prefix group → final URLs are /api/client/subscribe and /api/client/subscribe/confirm.
 */
Route::prefix('subscribe')->group(function () {
    Route::post('/', SubscribeController::class);
    // GET so the confirmation link in the email works when the user clicks it.
    Route::get('/confirm', ConfirmSubscriptionController::class);
});
