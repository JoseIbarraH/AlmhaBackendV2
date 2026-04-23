<?php

declare(strict_types=1);

namespace Src\Landing\Subscription\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Src\Landing\Subscription\Application\SubscribeUseCase;

final class SubscribeController
{
    private SubscribeUseCase $useCase;

    public function __construct(SubscribeUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Post(
        path: "/api/client/subscribe",
        summary: "Suscripción al newsletter",
        description: "Crea un Subscriber pendiente y dispatcha SendToN8nJob. El usuario debe confirmar via email para activar.",
        tags: ["Client / Subscription"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email"],
                properties: [new OA\Property(property: "email", type: "string", format: "email")]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Subscriber pendiente de confirmación"),
            new OA\Response(response: 400, description: "Error (email inválido o ya suscrito)"),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $this->useCase->execute($request->input('email'));

            return response()->json([
                'message' => 'Subscription pending. Please check your email.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to subscribe: ' . $e->getMessage(),
            ], 400);
        }
    }
}
