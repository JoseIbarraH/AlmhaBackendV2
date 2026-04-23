<?php

declare(strict_types=1);

namespace Src\Landing\Subscription\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Src\Landing\Subscription\Application\ConfirmSubscriptionUseCase;
use Src\Shared\Infrastructure\Http\ClientResponse;

final class ConfirmSubscriptionController
{
    public function __construct(private readonly ConfirmSubscriptionUseCase $useCase)
    {
    }

    #[OA\Get(
        path: "/api/client/subscribe/confirm",
        summary: "Confirma la suscripción vía token",
        description: "Llamado por la página SSR de confirmación del frontend (al usuario le llega un email con link que cae en esa página, que hace esta llamada internamente).",
        tags: ["Client / Subscription"],
        parameters: [
            new OA\Parameter(name: "token", in: "query", required: true, schema: new OA\Schema(type: "string")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Suscripción confirmada"),
            new OA\Response(response: 400, description: "Token ausente"),
            new OA\Response(response: 410, description: "Token inválido o expirado"),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $token = (string) $request->query('token', '');

        if ($token === '') {
            return ClientResponse::error('Token is required.', 400, ['status' => 'invalid']);
        }

        try {
            $this->useCase->execute($token);
            return ClientResponse::success(['status' => 'confirmed'], 'Subscription confirmed.');
        } catch (\Throwable $e) {
            return ClientResponse::error('Could not confirm subscription.', 410, ['status' => 'failed']);
        }
    }
}
