<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Landing\ContactData\Infrastructure\Controllers\GetContactDataController;

Route::get('/contact-data', GetContactDataController::class);
