<?php

declare(strict_types=1);

namespace Src\Admin\Team\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Team\Application\CreateTeamUseCase;
use Src\Admin\Team\Domain\Contracts\TeamRepositoryContract;
use Src\Shared\Infrastructure\Traits\StoresImages;
use Exception;

use OpenApi\Attributes as OA;

final class CreateTeamController
{
    use StoresImages;

    private CreateTeamUseCase $useCase;
    private TeamRepositoryContract $repository;

    public function __construct(CreateTeamUseCase $useCase, TeamRepositoryContract $repository)
    {
        $this->useCase = $useCase;
        $this->repository = $repository;
    }

    #[OA\Post(
        path: "/teams",
        summary: "Crear un nuevo miembro del equipo",
        tags: ["Team"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["image", "userId", "name", "specialty", "description"],
                    properties: [
                        new OA\Property(property: "image", type: "string", format: "binary", description: "Imagen de perfil"),
                        new OA\Property(property: "userId", type: "string", description: "UUID del usuario vinculado"),
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "specialty", type: "string"),
                        new OA\Property(property: "description", type: "string"),
                        new OA\Property(
                            property: "images",
                            type: "array",
                            items: new OA\Items(type: "string", format: "binary"),
                            description: "Galería de imágenes"
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Miembro creado exitosamente"
            ),
            new OA\Response(response: 422, description: "Error de validación"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'baseLang' => 'required|string|max:5',
            'name' => 'required|string|max:255',
            'status' => 'required|string|in:active,inactive',
            'userId' => 'nullable|string|exists:users,id',
            'image' => 'nullable|file|image|max:5120',
            'specialization' => 'nullable|string',
            'description' => 'nullable|string',
            'biography' => 'nullable|string',
            'gallery' => 'nullable|array',
            'gallery.*.path' => 'required|file|image|max:5120',
            'gallery.*.order' => 'numeric',
        ]);

        $baseLang = $request->input('baseLang');
        $configuredTargets = config('services.google_translate.targets', ['es', 'en']);
        $targetLanguages = array_values(array_diff($configuredTargets, [$baseLang]));

        try {
            // --- Handle Gallery Files ---
            $galleryData = [];
            if ($request->has('gallery')) {
                foreach ($request->file('gallery', []) as $index => $fileData) {
                    if (isset($fileData['path'])) {
                        $path = $this->storeImage(
                            $fileData['path'],
                            "teams/temp/gallery"
                        );
                        $galleryData[] = [
                            'path' => $path,
                            'order' => $request->input("gallery.$index.order", 0)
                        ];
                    }
                }
            }

            $teamId = $this->useCase->execute(
                $request->input('userId'),
                null, // slug (generated automatically)
                $baseLang,
                $request->input('name'),
                $request->input('status'),
                null, // image path (updated below)
                $request->input('specialization'),
                $request->input('description'),
                $request->input('biography'),
                $targetLanguages,
                $galleryData
            );

            // Update main image if provided
            if ($request->hasFile('image')) {
                $imageUrl = $this->storeImage($request->file('image'), "teams/{$teamId}/avatar");
                $this->repository->updateImage($teamId, $imageUrl);
            }

            return response()->json([
                'message' => 'Team member created successfully',
                'teamId' => $teamId,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
