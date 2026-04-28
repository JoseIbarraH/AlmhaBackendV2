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
        description: "Crea un Subscriber pendiente y encola un correo de confirmación. El usuario debe confirmar via email para activar.",
        tags: ["Client / Subscription"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "locale", type: "string", enum: ["es", "en", "fr"], description: "Idioma del cliente; usado para construir la URL de confirmación"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Subscriber pendiente de confirmación"),
            new OA\Response(response: 400, description: "Error (email inválido o ya suscrito)"),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $supported = implode(',', (array) config('app.supported_locales', ['es', 'en', 'fr']));

        $validated = $request->validate([
            'email'  => 'required|email',
            'locale' => 'nullable|string|in:' . $supported,
        ]);

        try {
            $this->useCase->execute($validated['email'], $validated['locale'] ?? null);

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
