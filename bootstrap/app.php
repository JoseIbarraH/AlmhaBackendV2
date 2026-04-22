<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
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
                    require base_path('src/Admin/User/Infrastructure/routes/api.php');
                    require base_path('src/Admin/Auth/Infrastructure/routes/api.php');
                    require base_path('src/Admin/Role/Infrastructure/routes/api.php');
                    require base_path('src/Admin/Blog/Infrastructure/routes/api.php');
                    require base_path('src/Admin/Procedure/Infrastructure/Routes/api.php');
                    require base_path('src/Admin/Team/Infrastructure/Routes/api.php');
                    require base_path('src/Admin/Audit/Infrastructure/Routes/api.php');
                    require base_path('src/Admin/Trash/Infrastructure/routes/api.php');
                    require base_path('src/Admin/Design/Infrastructure/Routes/api.php');
                    require base_path('src/Admin/Analytics/Infrastructure/routes/api.php');
                    require base_path('src/Admin/Settings/Infrastructure/Routes/api.php');
                    require base_path('src/Landing/Contact/Infrastructure/Routes/api.php');
                    require base_path('src/Landing/Chat/Infrastructure/Routes/api.php');

                    // Setup Inicial
                    require base_path('src/Admin/User/Infrastructure/routes/instance_setup.php');
                });

            // Public client-facing read endpoints consumed by AlmhaFrontendClient
            Route::middleware('api')
                ->prefix('api/client')
                ->group(function () {
                    require base_path('src/Landing/Maintenance/Infrastructure/Routes/api.php');
                    require base_path('src/Landing/Blog/Infrastructure/Routes/api.php');
                    require base_path('src/Landing/Procedure/Infrastructure/Routes/api.php');
                    require base_path('src/Landing/Member/Infrastructure/Routes/api.php');
                    require base_path('src/Landing/ContactData/Infrastructure/Routes/api.php');
                    require base_path('src/Landing/Navbar/Infrastructure/Routes/api.php');
                    require base_path('src/Landing/Home/Infrastructure/Routes/api.php');
                    require base_path('src/Landing/Subscription/Infrastructure/Routes/api.php');
                });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => null);

        $middleware->api(prepend: [
            HandleCors::class,
        ]);

        $middleware->api(append: [
            \Src\Shared\Infrastructure\Middleware\AuditLogMiddleware::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => 'unauthenticated',
                    'message' => 'No autorizado.',
                ], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            return response()->json([
                'error' => 'validation_error',
                'message' => 'Los datos proporcionados no son válidos.',
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            return response()->json([
                'error' => 'not_found',
                'message' => 'Recurso no encontrado.',
            ], 404);
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            return response()->json([
                'error' => 'forbidden',
                'message' => 'No tienes permiso para realizar esta acción.',
            ], 403);
        });

        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
            return response()->json([
                'error' => 'forbidden',
                'message' => 'No tienes permiso para realizar esta acción.',
            ], 403);
        });

        $exceptions->shouldRenderJsonWhen(function ($request, $e) {
            return true;
        });
    })->create();
