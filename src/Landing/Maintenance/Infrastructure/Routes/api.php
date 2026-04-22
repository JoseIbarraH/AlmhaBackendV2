<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Landing\Maintenance\Infrastructure\Controllers\GetMaintenanceController;

Route::get('/maintenance', GetMaintenanceController::class);
