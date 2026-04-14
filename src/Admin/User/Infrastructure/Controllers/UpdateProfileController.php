<?php

declare(strict_types=1);

namespace Src\Admin\User\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Src\Admin\User\Application\UpdateProfileUseCase;
use OpenApi\Attributes as OA;

final class UpdateProfileController extends Controller
{
    private UpdateProfileUseCase $useCase;

    public function __construct(UpdateProfileUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Post(
        path: "/profile/update",
        summary: "Actualizar perfil del usuario autenticado",
        tags: ["Profile"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Nuevo Nombre"),
                    new OA\Property(property: "password", type: "string", format: "password", nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Perfil actualizado correctamente"),
            new OA\Response(response: 422, description: "Error de validación")
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|confirmed'
        ]);

        try {
            $this->useCase->execute(
                (string)$request->user()->id,
                $request->input('name'),
                $request->input('password')
            );

            return response()->json(['message' => 'Profile updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
