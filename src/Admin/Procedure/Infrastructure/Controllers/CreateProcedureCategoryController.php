<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Src\Admin\Procedure\Application\CreateProcedureCategoryUseCase;
use Exception;

use OpenApi\Attributes as OA;

final class CreateProcedureCategoryController
{
    private CreateProcedureCategoryUseCase $useCase;

    public function __construct(CreateProcedureCategoryUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Post(
        path: "/procedure-categories",
        summary: "Crear una nueva categoría de procedimientos",
        tags: ["Procedure"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["code", "baseLang", "title"],
                properties: [
                    new OA\Property(property: "code", type: "string", example: "CIRUGIA_ESTETICA"),
                    new OA\Property(property: "baseLang", type: "string", example: "es"),
                    new OA\Property(property: "title", type: "string", example: "Cirugía Estética")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Categoría creada exitosamente"
            ),
            new OA\Response(response: 422, description: "Error de validación"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'nullable|string|max:255|unique:procedure_categories,code',
            'baseLang' => 'required|string|max:5',
            'title' => 'required|string|max:255'
        ]);

        // Auto-generate code if not provided
        $code = $request->input('code') ?: Str::random(8);

        $baseLang = $request->input('baseLang');
        $configuredTargets = config('services.google_translate.targets', ['es', 'en']);
        $targetLanguages = array_values(array_diff($configuredTargets, [$baseLang]));

        try {
            $this->useCase->execute(
                $code,
                $baseLang,
                $request->input('title'),
                $targetLanguages
            );

            return response()->json([
                'message' => 'Procedure category created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
