<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Admin\Audit\Infrastructure\Controllers\GetAuditsController;

Route::prefix('audits')
    ->middleware(['auth:api', 'permission:view_audits'])
    ->group(function () {
    Route::get('/', GetAuditsController::class);
});
