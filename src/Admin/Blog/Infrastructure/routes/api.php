<?php

use Illuminate\Support\Facades\Route;
use Src\Admin\Blog\Infrastructure\Controllers\CreateBlogCategoryController;
use Src\Admin\Blog\Infrastructure\Controllers\GetAllBlogCategoriesController;
use Src\Admin\Blog\Infrastructure\Controllers\CreateBlogController;
use Src\Admin\Blog\Infrastructure\Controllers\GetAllBlogsController;
use Src\Admin\Blog\Infrastructure\Controllers\GetBlogController;
use Src\Admin\Blog\Infrastructure\Controllers\ChangeBlogStatusController;
use Src\Admin\Blog\Infrastructure\Controllers\UpdateBlogController;
use Src\Admin\Blog\Infrastructure\Controllers\DeleteBlogController;

Route::prefix('blog-categories')->group(function () {
    Route::post('/', CreateBlogCategoryController::class);
    Route::get('/', GetAllBlogCategoriesController::class);
});

Route::prefix('blogs')->group(function () {
    Route::post('/', CreateBlogController::class);
    Route::get('/', GetAllBlogsController::class);
    Route::get('/{slug}', GetBlogController::class);
    Route::put('/{id}', UpdateBlogController::class);
    Route::delete('/{id}', DeleteBlogController::class);
    Route::patch('/{id}/status', ChangeBlogStatusController::class);
});
