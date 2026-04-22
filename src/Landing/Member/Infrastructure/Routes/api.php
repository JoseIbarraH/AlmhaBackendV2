<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Landing\Member\Infrastructure\Controllers\GetMemberBySlugController;
use Src\Landing\Member\Infrastructure\Controllers\GetMemberListController;

Route::prefix('members')->group(function () {
    Route::get('/', GetMemberListController::class);
    Route::get('/{slug}', GetMemberBySlugController::class);
});
