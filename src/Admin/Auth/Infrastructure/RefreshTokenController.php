<?php

namespace Src\Admin\Auth\Infrastructure;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Admin\Auth\Application\RefreshTokenUseCase;

use OpenApi\Attributes as OA;

class RefreshTokenController extends Controller
{
    private RefreshTokenUseCase $useCase;

    public function __construct(RefreshTokenUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Post(
        path: "/auth/refresh",
        summary: "Refrescar token",
        tags: ["Auth"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Token refrescado exitosamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "access_token", type: "string"),
                        new OA\Property(property: "token_type", type: "string", example: "bearer"),
                        new OA\Property(property: "expires_in", type: "integer", example: 3600)
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Token inválido o expirado")
        ]
    )]
    public function __invoke(): JsonResponse
    {
        $authToken = $this->useCase->execute();

        return response()->json([
            'access_token' => $authToken->value(),
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl', 60) * 60
        ], 200);
    }
}
