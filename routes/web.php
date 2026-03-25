<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'service' => 'My API Name',
        'status' => 'running',
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version()
    ]);
});
