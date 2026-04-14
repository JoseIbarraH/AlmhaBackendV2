<?php

use Illuminate\Support\Facades\Route;

use Src\Admin\Design\Infrastructure\Controllers\GetDesignsController;
use Src\Admin\Design\Infrastructure\Controllers\UpdateDesignModeController;
use Src\Admin\Design\Infrastructure\Controllers\UpdateDesignStatusController;
use Src\Admin\Design\Infrastructure\Controllers\SaveDesignItemController;
use Src\Admin\Design\Infrastructure\Controllers\UpdateDesignItemController;
use Src\Admin\Design\Infrastructure\Controllers\DeleteDesignItemController;

Route::middleware('auth:api')->prefix('designs')->group(function () {
    Route::get('/', GetDesignsController::class);
    Route::put('/{designId}/mode', UpdateDesignModeController::class);
    Route::put('/{designId}/status', UpdateDesignStatusController::class);
    Route::post('/items', SaveDesignItemController::class);
    Route::post('/items/{itemId}', UpdateDesignItemController::class); // POST using multipart/form-data to simulate PUT for files
    Route::delete('/items/{itemId}', DeleteDesignItemController::class);
});

// Create an open public route to fetch the designs
Route::prefix('public/designs')->group(function () {
    Route::get('/', GetDesignsController::class);
});
