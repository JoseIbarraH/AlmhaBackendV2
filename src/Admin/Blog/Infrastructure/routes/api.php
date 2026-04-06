<?php

use Illuminate\Support\Facades\Route;
use Src\Admin\Blog\Infrastructure\Controllers\CreateBlogCategoryController;
use Src\Admin\Blog\Infrastructure\Controllers\GetAllBlogCategoriesController;
use Src\Admin\Blog\Infrastructure\Controllers\UpdateBlogCategoryController;
use Src\Admin\Blog\Infrastructure\Controllers\DeleteBlogCategoryController;

use Src\Admin\Blog\Infrastructure\Controllers\CreateBlogController;
use Src\Admin\Blog\Infrastructure\Controllers\GetAllBlogsController;
use Src\Admin\Blog\Infrastructure\Controllers\GetBlogController;
use Src\Admin\Blog\Infrastructure\Controllers\ChangeBlogStatusController;
use Src\Admin\Blog\Infrastructure\Controllers\UpdateBlogController;
use Src\Admin\Blog\Infrastructure\Controllers\DeleteBlogController;
use Src\Admin\Blog\Infrastructure\Controllers\UploadBlogMediaController;
use Src\Admin\Blog\Infrastructure\Controllers\DeleteBlogMediaController;

Route::prefix('blog-categories')->middleware(['auth:api'])->group(function () {
    Route::post('/', CreateBlogCategoryController::class)->middleware('permission:create_blog_categories');
    Route::get('/', GetAllBlogCategoriesController::class)->middleware('permission:view_blog_categories');
    Route::post('/{id}', UpdateBlogCategoryController::class)->middleware('permission:edit_blog_categories');
    Route::delete('/{id}', DeleteBlogCategoryController::class)->middleware('permission:delete_blog_categories');
});

Route::prefix('blogs')->middleware(['auth:api'])->group(function () {
    Route::post('/', CreateBlogController::class)->middleware('permission:create_blogs');
    Route::get('/', GetAllBlogsController::class)->middleware('permission:view_blogs');
    Route::get('/{id}', GetBlogController::class)->middleware('permission:view_blog_detail');
    Route::post('/{id}', UpdateBlogController::class)->middleware('permission:edit_blogs');
    Route::delete('/{id}', DeleteBlogController::class)->middleware('permission:delete_blogs');
    Route::patch('/{id}/status', ChangeBlogStatusController::class)->middleware('permission:change_blog_status');

    // Media
    Route::post('/{id}/media', UploadBlogMediaController::class);
    Route::delete('/{id}/media', DeleteBlogMediaController::class);
});
