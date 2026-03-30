<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Blog\Application\GetBlogUseCase;
use Exception;

use OpenApi\Attributes as OA;

final class GetBlogController
{
    private GetBlogUseCase $useCase;

    public function __construct(GetBlogUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Get(
        path: "/blogs/{id}",
        summary: "Obtener detalle de una entrada de blog",
        tags: ["Blog"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del blog",
                schema: new OA\Schema(type: "integer")
            ),
            new OA\Parameter(
                name: "Accept-Language",
                in: "header",
                required: false,
                description: "Idioma de los contenidos (es, en)",
                schema: new OA\Schema(type: "string", default: "es")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detalle del blog",
                content: new OA\JsonContent(type: "object")
            ),
            new OA\Response(response: 404, description: "No encontrado"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(Request $request, int $id): JsonResponse
    {
        try {
            $lang = $request->header('Accept-Language', 'es');
            $blog = $this->useCase->execute($id, $lang);
            
            return response()->json([
                'data' => $blog
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
