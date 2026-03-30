<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Blog\Application\ChangeBlogStatusUseCase;
use Exception;

use OpenApi\Attributes as OA;

final class ChangeBlogStatusController
{
    private ChangeBlogStatusUseCase $useCase;

    public function __construct(ChangeBlogStatusUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Patch(
        path: "/blogs/{id}/status",
        summary: "Cambiar el estado de una entrada de blog",
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
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["status"],
                properties: [
                    new OA\Property(property: "status", type: "string", enum: ["draft", "published", "archived"])
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Estado actualizado exitosamente"
            ),
            new OA\Response(response: 404, description: "No encontrado"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:draft,published,archived'
        ]);

        try {
            $this->useCase->execute($id, $request->input('status'));

            return response()->json([
                'message' => 'Blog status updated successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
