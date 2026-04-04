<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Blog\Application\GetAllBlogsUseCase;
use Exception;

use OpenApi\Attributes as OA;

final class GetAllBlogsController
{
    private GetAllBlogsUseCase $useCase;

    public function __construct(GetAllBlogsUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Get(
        path: "/blogs",
        summary: "Listar todas las entradas de blog",
        tags: ["Blog"],
        security: [["bearerAuth" => []]],
        parameters: [
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
                description: "Lista de blogs",
                content: new OA\JsonContent(type: "array", items: new OA\Items(type: "object"))
            ),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $lang = $request->header('Accept-Language', 'es');
            $page = (int) $request->query('page', '1');
            $perPage = (int) $request->query('per_page', '15');
            $search = $request->query('search');
            $blogs = $this->useCase->execute($lang, $page, $perPage, $search);
            
            return response()->json([
                'data' => $blogs['items'],
                'meta' => $blogs['meta']
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
