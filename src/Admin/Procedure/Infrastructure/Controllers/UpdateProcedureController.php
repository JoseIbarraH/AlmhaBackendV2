<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Procedure\Application\UpdateProcedureUseCase;
use Src\Admin\Procedure\Domain\Contracts\ProcedureRepositoryContract;
use Src\Shared\Infrastructure\Traits\StoresImages;
use Exception;

use OpenApi\Attributes as OA;

final class UpdateProcedureController
{
    use StoresImages;

    private UpdateProcedureUseCase $useCase;
    private ProcedureRepositoryContract $repository;

    public function __construct(UpdateProcedureUseCase $useCase, ProcedureRepositoryContract $repository)
    {
        $this->useCase = $useCase;
        $this->repository = $repository;
    }

    #[OA\Post(
        path: "/procedures/{id}",
        summary: "Actualizar un procedimiento",
        tags: ["Procedure"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del procedimiento",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["categoryCode", "baseLang", "title", "status"],
                    properties: [
                        new OA\Property(property: "categoryCode", type: "string", example: "CIRUGIA_ESTETICA"),
                        new OA\Property(property: "baseLang", type: "string", example: "es"),
                        new OA\Property(property: "title", type: "string"),
                        new OA\Property(property: "subtitle", type: "string"),
                        new OA\Property(property: "status", type: "string", enum: ["draft", "published", "archived"]),
                        new OA\Property(property: "userId", type: "string"),
                        new OA\Property(property: "image", type: "string", format: "binary", description: "Nueva imagen principal"),
                        new OA\Property(
                            property: "sections",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "type", type: "string"),
                                    new OA\Property(property: "title", type: "string"),
                                    new OA\Property(property: "contentOne", type: "string")
                                ]
                            )
                        ),
                        new OA\Property(
                            property: "gallery",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "path", type: "string", format: "binary"),
                                    new OA\Property(property: "type", type: "string"),
                                    new OA\Property(property: "order", type: "integer")
                                ]
                            )
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Procedimiento actualizado exitosamente"
            ),
            new OA\Response(response: 404, description: "No encontrado"),
            new OA\Response(response: 422, description: "Error de validación"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'categoryCode' => 'required|string|exists:procedure_categories,code',
            'baseLang' => 'required|string|max:5',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'status' => 'required|string|in:draft,published,archived',
            'userId' => 'nullable|string|exists:users,id',
            'image' => 'nullable', // Puede ser un archivo nuevo o un string de la ruta existente

            // Sections
            'sections' => 'nullable|array',
            'sections.*.type' => 'required|string',
            'sections.*.title' => 'nullable|string',
            'sections.*.contentOne' => 'nullable|string',
            'sections.*.contentTwo' => 'nullable|string',

            // FAQs
            'faqs' => 'nullable|array',
            'faqs.*.question' => 'required|string',
            'faqs.*.answer' => 'required|string',
            'faqs.*.order' => 'numeric',

            // Instructions
            'postoperativeInstructions' => 'nullable|array',
            'postoperativeInstructions.*.type' => 'required|string',
            'postoperativeInstructions.*.content' => 'required|string',
            'postoperativeInstructions.*.order' => 'numeric',

            // Preparation Steps
            'preparationSteps' => 'nullable|array',
            'preparationSteps.*.title' => 'required|string',
            'preparationSteps.*.description' => 'nullable|string',
            'preparationSteps.*.order' => 'numeric',

            // Recovery Phases
            'recoveryPhases' => 'nullable|array',
            'recoveryPhases.*.period' => 'nullable|string',
            'recoveryPhases.*.title' => 'required|string',
            'recoveryPhases.*.description' => 'nullable|string',
            'recoveryPhases.*.order' => 'numeric',

            // Gallery
            'gallery' => 'nullable|array',
            'gallery.*.path' => 'nullable', // Puede ser un string (imagen existente) o un File (nueva imagen)
            'gallery.*.type' => 'required|string',
            'gallery.*.pairId' => 'nullable|numeric',
            'gallery.*.order' => 'numeric',
        ]);

        $baseLang = $request->input('baseLang');
        $configuredTargets = config('services.google_translate.targets', ['es', 'en']);
        $targetLanguages = array_values(array_diff($configuredTargets, [$baseLang]));

        try {
            $imageUrl = $request->input('image');
            if ($request->hasFile('image')) {
                $imageUrl = $this->storeImage($request->file('image'), "procedures/{$id}/main_image", true);
            }

            // --- Handle Gallery Files ---
            $galleryData = $request->input('gallery', []);
            $galleryFiles = $request->file('gallery', []);
            
            $filteredGalleryData = [];

            foreach ($galleryData as $index => &$item) {
                // Si viene un archivo nuevo, lo subimos
                if (isset($galleryFiles[$index]['path'])) {
                    $item['path'] = $this->storeImage(
                        $galleryFiles[$index]['path'],
                        "procedures/{$id}/gallery"
                    );
                }
                
                // Validar que efectivamente tenga un path, omitir vacíos
                if (!empty($item['path'])) {
                    $filteredGalleryData[] = $item;
                }
            }

            $this->useCase->execute(
                $id,
                $request->input('categoryCode'),
                $baseLang,
                $request->input('title'),
                $request->input('subtitle'),
                $targetLanguages,
                $request->input('status'),
                $request->input('userId'),
                $imageUrl,
                $request->input('sections', []),
                $request->input('faqs', []),
                $request->input('postoperativeInstructions', []),
                $request->input('preparationSteps', []),
                $request->input('recoveryPhases', []),
                $filteredGalleryData
            );

            return response()->json([
                'message' => 'Procedure updated successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage() . ' in ' . basename($e->getFile()) . ':' . $e->getLine()
            ], 400);
        }
    }
}
