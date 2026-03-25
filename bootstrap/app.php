<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api/v1')      // El prefijo global para todos los módulos aquí
                ->group(function () {

                    // Modulo de Users
                    require base_path('src/admin/User/Infrastructure/routes/api.php');
                    require base_path('src/Admin/Auth/Infrastructure/routes/api.php');
                    require base_path('src/Admin/Role/Infrastructure/routes/api.php');
                    require base_path('src/Admin/Blog/Infrastructure/routes/api.php');
                    // ⬇️ EJEMPLOS de cómo agregarías futuros módulos aquí abajo ⬇️
                    // require base_path('src/admin/Product/Infrastructure/routes/api.php');
                    // require base_path('src/admin/Order/Infrastructure/routes/api.php');
                    // require base_path('src/admin/Payment/Infrastructure/routes/api.php');

                });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Esto detecta si la petición falló y fuerza la respuesta JSON
        $exceptions->shouldRenderJsonWhen(function ($request, $e) {
            return true;
        });
    })->create();
