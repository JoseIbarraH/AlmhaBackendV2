<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Procedure\Application\UpdateProcedureCategoryUseCase;
use Exception;

use OpenApi\Attributes as OA;

final class UpdateProcedureCategoryController
{
    private UpdateProcedureCategoryUseCase $useCase;

    public function __construct(UpdateProcedureCategoryUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Post(
        path: "/procedure-categories/{id}",
        summary: "Actualizar una categoría de procedimientos",
        tags: ["Procedure"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la categoría",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["code", "baseLang", "title"],
                properties: [
                    new OA\Property(property: "code", type: "string", example: "CIRUGIA_ESTETICA"),
                    new OA\Property(property: "baseLang", type: "string", example: "es"),
                    new OA\Property(property: "title", type: "string", example: "Cirugía Estética Actualizada")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Categoría actualizada exitosamente"
            ),
            new OA\Response(response: 404, description: "No encontrado"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:255|unique:procedure_categories,code,' . $id,
            'baseLang' => 'required|string|max:5',
            'title' => 'required|string|max:255',
        ]);

        $baseLang = $request->input('baseLang');
        $configuredTargets = config('services.google_translate.targets', ['es', 'en']);
        $targetLanguages = array_values(array_diff($configuredTargets, [$baseLang]));

        try {
            $this->useCase->execute(
                $id,
                $request->input('code'),
                $baseLang,
                $request->input('title'),
                $targetLanguages
            );

            return response()->json([
                'message' => 'Procedure category updated successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
