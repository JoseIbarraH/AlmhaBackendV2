<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Procedure\Application\CreateProcedureUseCase;
use Src\Admin\Procedure\Domain\Contracts\ProcedureRepositoryContract;
use Src\Shared\Infrastructure\Traits\StoresImages;
use Exception;

use OpenApi\Attributes as OA;

final class CreateProcedureController
{
    use StoresImages;

    private CreateProcedureUseCase $useCase;
    private ProcedureRepositoryContract $repository;

    public function __construct(CreateProcedureUseCase $useCase, ProcedureRepositoryContract $repository)
    {
        $this->useCase = $useCase;
        $this->repository = $repository;
    }

    #[OA\Post(
        path: "/procedures",
        summary: "Crear un nuevo procedimiento",
        tags: ["Procedure"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["categoryCode", "baseLang", "title", "userId"],
                    properties: [
                        new OA\Property(property: "categoryCode", type: "string", example: "CIRUGIA_ESTETICA"),
                        new OA\Property(property: "baseLang", type: "string", example: "es"),
                        new OA\Property(property: "title", type: "string", example: "Rinoplastia"),
                        new OA\Property(property: "subtitle", type: "string"),
                        new OA\Property(property: "userId", type: "string"),
                        new OA\Property(property: "image", type: "string", format: "binary", description: "Imagen principal"),
                        new OA\Property(
                            property: "sections",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "type", type: "string"),
                                    new OA\Property(property: "title", type: "string"),
                                    new OA\Property(property: "contentOne", type: "string"),
                                    new OA\Property(property: "order", type: "integer")
                                ]
                            )
                        ),
                        new OA\Property(
                            property: "faqs",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "question", type: "string"),
                                    new OA\Property(property: "answer", type: "string"),
                                    new OA\Property(property: "order", type: "integer")
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
                response: 201,
                description: "Procedimiento creado exitosamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(property: "procedureId", type: "integer")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Error de validación"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'categoryCode' => 'required|string|exists:procedure_categories,code',
            'baseLang' => 'required|string|max:5',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'userId' => 'nullable|string|exists:users,id',
            'image' => 'nullable|file|image|max:5120',

            // Sections
            'sections' => 'nullable|array',
            'sections.*.type' => 'required|string',
            'sections.*.title' => 'nullable|string',
            'sections.*.contentOne' => 'nullable|string',
            'sections.*.contentTwo' => 'nullable|string',
            'sections.*.order' => 'integer',

            // FAQs
            'faqs' => 'nullable|array',
            'faqs.*.question' => 'required|string',
            'faqs.*.answer' => 'required|string',
            'faqs.*.order' => 'integer',

            // Instructions
            'postoperativeInstructions' => 'nullable|array',
            'postoperativeInstructions.*.type' => 'required|string',
            'postoperativeInstructions.*.content' => 'required|string',
            'postoperativeInstructions.*.order' => 'integer',

            // Preparation Steps
            'preparationSteps' => 'nullable|array',
            'preparationSteps.*.title' => 'required|string',
            'preparationSteps.*.description' => 'nullable|string',
            'preparationSteps.*.order' => 'integer',

            // Recovery Phases
            'recoveryPhases' => 'nullable|array',
            'recoveryPhases.*.period' => 'nullable|string',
            'recoveryPhases.*.title' => 'required|string',
            'recoveryPhases.*.description' => 'nullable|string',
            'recoveryPhases.*.order' => 'integer',

            // Gallery (Paths only for now, actual upload handled separately or as placeholder)
            'gallery' => 'nullable|array',
            'gallery.*.path' => 'required|nullable|file|image|max:5120',
            'gallery.*.type' => 'required|string',
            'gallery.*.pairId' => 'nullable|integer',
            'gallery.*.order' => 'integer',
        ]);

        $baseLang = $request->input('baseLang');
        $configuredTargets = config('services.google_translate.targets', ['es', 'en']);
        $targetLanguages = array_values(array_diff($configuredTargets, [$baseLang]));
        try {
            // --- Handle Gallery Files ---
            $galleryData = $request->input('gallery', []);
            $galleryFiles = $request->file('gallery', []);

            foreach ($galleryData as $index => &$item) {
                if (isset($galleryFiles[$index]['path'])) {
                    $item['path'] = $this->storeImage(
                        $galleryFiles[$index]['path'],
                        "procedures/temp/gallery" // temp path until procedureId is known
                    );
                }
            }

            $procedureId = $this->useCase->execute(
                $request->input('categoryCode'),
                $baseLang,
                $request->input('title'),
                $request->input('subtitle'),
                $targetLanguages,
                'draft',
                $request->input('userId'),
                null, // main image
                $request->input('sections', []),
                $request->input('faqs', []),
                $request->input('postoperativeInstructions', []),
                $request->input('preparationSteps', []),
                $request->input('recoveryPhases', []),
                $galleryData
            );

            // Subir imagen principal a MinIO si se envió
            if ($request->hasFile('image')) {
                $imageUrl = $this->storeImage($request->file('image'), "procedures/{$procedureId}/main_image");
                $this->repository->updateImage($procedureId, $imageUrl);
            }

            return response()->json([
                'message' => 'Procedure created successfully',
                'procedureId' => $procedureId,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
