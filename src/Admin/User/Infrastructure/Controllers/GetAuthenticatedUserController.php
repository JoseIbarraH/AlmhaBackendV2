<?php

declare(strict_types=1);

namespace Src\Admin\User\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;

final class GetAuthenticatedUserController extends Controller
{
    #[OA\Get(
        path: "/user",
        summary: "Obtener usuario autenticado",
        tags: ["User"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Datos del usuario",
                content: new OA\JsonContent(type: "object")
            ),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json($request->user(), 200);
    }
}
