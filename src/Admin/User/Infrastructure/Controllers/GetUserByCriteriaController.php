<?php

declare(strict_types=1);

namespace Src\Admin\User\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\User\Application\GetUserByCriteriaUseCase;
use Exception;

use OpenApi\Attributes as OA;

final class GetUserByCriteriaController
{
    private GetUserByCriteriaUseCase $useCase;

    public function __construct(GetUserByCriteriaUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Get(
        path: "/users/search",
        summary: "Buscar usuarios por criterios",
        tags: ["User"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "term",
                in: "query",
                required: false,
                description: "Término de búsqueda general",
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "name",
                in: "query",
                required: false,
                description: "Filtrar por nombre",
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "email",
                in: "query",
                required: false,
                description: "Filtrar por email",
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Resultados de la búsqueda",
                content: new OA\JsonContent(type: "array", items: new OA\Items(type: "object"))
            ),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'term' => 'nullable|string',
            'name' => 'nullable|string',
            'email' => 'nullable|string',
        ]);

        try {
            $users = $this->useCase->execute(
                $request->query('term'),
                $request->query('name'),
                $request->query('email')
            );
            
            $data = array_map(function($user) {
                return [
                    'id' => $user->id() ? $user->id()->value() : null,
                    'name' => $user->name()->value(),
                    'email' => $user->email()->value(),
                    'is_active' => $user->status()->value(),
                    'roles' => $user->roles(),
                ];
            }, $users);

            return response()->json([
                'data' => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
