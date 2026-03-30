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


Route::prefix('procedure-categories')->middleware(['auth:api'])->group(function () {
    Route::post('/', CreateProcedureCategoryController::class)->middleware('permission:create_procedure_categories');
    Route::get('/', GetAllProcedureCategoriesController::class)->middleware('permission:view_procedure_categories');
    Route::post('/{id}', UpdateProcedureCategoryController::class)->middleware('permission:edit_procedure_categories');
    Route::delete('/{id}', DeleteProcedureCategoryController::class)->middleware('permission:delete_procedure_categories');
});

Route::prefix('procedures')->middleware(['auth:api'])->group(function () {
    Route::get('/', GetAllProceduresController::class)->middleware('permission:view_procedures');
    Route::get('/{id}', GetProcedureController::class)->middleware('permission:view_procedure_detail');
    Route::post('/', CreateProcedureController::class)->middleware('permission:create_procedures');
    Route::post('/{id}', UpdateProcedureController::class)->middleware('permission:edit_procedures'); // POST para soportar FormData con imágenes en actualizaciones
    Route::delete('/{id}', DeleteProcedureController::class)->middleware('permission:delete_procedures');
});

