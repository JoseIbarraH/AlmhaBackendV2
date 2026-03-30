<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Src\Admin\Audit\Application\RecordAuditUseCase;
use Illuminate\Support\Facades\Auth;

final class AuditLogMiddleware
{
    private RecordAuditUseCase $useCase;

    public function __construct(RecordAuditUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // No registrar GET segun peticion del usuario
        if ($request->isMethod('GET')) {
            return $response;
        }

        $this->logAction($request, $response);

        return $response;
    }

    private function logAction(Request $request, Response $response): void
    {
        $user = Auth::guard('api')->user();
        $payload = $request->except(['password', 'password_confirmation', 'old_password']);

        $this->useCase->execute(
            $user ? (string) $user->id : null,
            $request->method(),
            $request->fullUrl(),
            $payload,
            $response->getStatusCode(),
            $request->ip(),
            $request->userAgent(),
            $this->determineAction($request)
        );
    }

    private function determineAction(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        return "{$method} {$path}";
    }
}
