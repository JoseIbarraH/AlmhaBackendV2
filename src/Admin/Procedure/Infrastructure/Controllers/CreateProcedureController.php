<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Procedure\Application\CreateProcedureUseCase;
use Src\Admin\Procedure\Domain\Contracts\ProcedureRepositoryContract;
use Src\Shared\Infrastructure\Traits\StoresImages;
use Exception;

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
