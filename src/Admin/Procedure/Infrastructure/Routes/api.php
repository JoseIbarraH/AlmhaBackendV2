<?php

use Illuminate\Support\Facades\Route;
use Src\Admin\Procedure\Infrastructure\Controllers\CreateProcedureController;
use Src\Admin\Procedure\Infrastructure\Controllers\DeleteProcedureController;
use Src\Admin\Procedure\Infrastructure\Controllers\GetAllProceduresController;
use Src\Admin\Procedure\Infrastructure\Controllers\GetProcedureController;
use Src\Admin\Procedure\Infrastructure\Controllers\UpdateProcedureController;
use Src\Admin\Procedure\Infrastructure\Controllers\CreateProcedureCategoryController;
use Src\Admin\Procedure\Infrastructure\Controllers\GetAllProcedureCategoriesController;
use Src\Admin\Procedure\Infrastructure\Controllers\UpdateProcedureCategoryController;
use Src\Admin\Procedure\Infrastructure\Controllers\DeleteProcedureCategoryController;


Route::prefix('procedure-categories')->group(function () {
    Route::post('/', CreateProcedureCategoryController::class);
    Route::get('/', GetAllProcedureCategoriesController::class);
    Route::put('/{id}', UpdateProcedureCategoryController::class);
    Route::delete('/{id}', DeleteProcedureCategoryController::class);
});

Route::prefix('procedures')->group(function () {
    Route::get('/', GetAllProceduresController::class);
    Route::get('/{id}', GetProcedureController::class);
    Route::post('/', CreateProcedureController::class);
    Route::post('/{id}', UpdateProcedureController::class); // POST para soportar FormData con imágenes en actualizaciones
    Route::delete('/{id}', DeleteProcedureController::class);
});

