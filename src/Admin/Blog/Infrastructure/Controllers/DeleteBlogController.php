<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Admin\Blog\Application\DeleteBlogUseCase;
use Src\Shared\Infrastructure\Http\ApiResponse;

use OpenApi\Attributes as OA;

final class DeleteBlogController
{
    private DeleteBlogUseCase $useCase;

    public function __construct(DeleteBlogUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Delete(
        path: "/blogs/{id}",
        summary: "Eliminar una entrada de blog",
        tags: ["Blog"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del blog",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Blog eliminado exitosamente"
            ),
            new OA\Response(response: 404, description: "No encontrado"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(int $id): JsonResponse
    {
        $this->useCase->execute($id);

        return ApiResponse::success(message: 'Blog deleted successfully');
    }
}
