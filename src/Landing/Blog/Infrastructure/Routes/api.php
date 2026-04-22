<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Landing\Blog\Infrastructure\Controllers\GetBlogBySlugController;
use Src\Landing\Blog\Infrastructure\Controllers\GetBlogListController;

Route::prefix('blog')->group(function () {
    Route::get('/', GetBlogListController::class);
    Route::get('/{slug}', GetBlogBySlugController::class);
});
