<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Landing\Procedure\Infrastructure\Controllers\GetProcedureBySlugController;
use Src\Landing\Procedure\Infrastructure\Controllers\GetProcedureListController;

Route::prefix('procedure')->group(function () {
    Route::get('/', GetProcedureListController::class);
    Route::get('/{slug}', GetProcedureBySlugController::class);
});
