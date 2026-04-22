<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Landing\Navbar\Infrastructure\Controllers\GetNavbarDataController;

Route::get('/navbar-data', GetNavbarDataController::class);
