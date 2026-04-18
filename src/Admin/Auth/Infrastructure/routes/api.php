<?php

use Illuminate\Support\Facades\Route;
use Src\Admin\Auth\Infrastructure\LoginController;
use Src\Admin\Auth\Infrastructure\RefreshTokenController;
use Src\Admin\Auth\Infrastructure\VerifyEmailController;
use Src\Admin\Auth\Infrastructure\ResendVerificationController;

Route::prefix('auth')->group(function () {
    Route::post('login', LoginController::class);
    Route::post('refresh', RefreshTokenController::class)->middleware('auth:api');
    
    // Rutas de verificación de email
    Route::get('email/verify/{token}', VerifyEmailController::class)->name('verification.verify');
    Route::post('email/resend', ResendVerificationController::class);
});
