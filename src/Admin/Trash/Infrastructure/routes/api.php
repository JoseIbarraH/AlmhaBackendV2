<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Admin\Trash\Infrastructure\Controllers\GetTrashController;
use Src\Admin\Trash\Infrastructure\Controllers\RestoreTrashController;
use Src\Admin\Trash\Infrastructure\Controllers\DeleteTrashController;

Route::middleware(['auth:api', 'role:admin'])->prefix('trash')->group(function () {
    Route::get('/', GetTrashController::class);
    Route::post('/{type}/{id}/restore', RestoreTrashController::class);
    Route::delete('/{type}/{id}/permanent', DeleteTrashController::class);
});
