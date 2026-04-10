<?php

declare(strict_types=1);

namespace Src\Admin\Team\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Team\Application\UpdateTeamUseCase;
use Src\Admin\Team\Domain\Contracts\TeamRepositoryContract;
use Src\Shared\Infrastructure\Traits\StoresImages;
use Exception;

use OpenApi\Attributes as OA;

final class UpdateTeamController
{
    use StoresImages;

    private UpdateTeamUseCase $useCase;
    private TeamRepositoryContract $repository;

    public function __construct(UpdateTeamUseCase $useCase, TeamRepositoryContract $repository)
    {
        $this->useCase = $useCase;
        $this->repository = $repository;
    }

    #[OA\Post(
        path: "/teams/{id}",
        summary: "Actualizar un miembro del equipo",
        tags: ["Team"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del miembro del equipo",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["baseLang", "name", "status"],
                    properties: [
                        new OA\Property(property: "baseLang", type: "string", example: "es"),
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "status", type: "string", enum: ["active", "inactive"]),
                        new OA\Property(property: "userId", type: "string"),
                        new OA\Property(property: "image", type: "string", format: "binary", description: "Nueva imagen de perfil"),
                        new OA\Property(property: "specialization", type: "string"),
                        new OA\Property(property: "description", type: "string"),
                        new OA\Property(property: "biography", type: "string"),
                        new OA\Property(
                            property: "gallery",
                            type: "array",
                            items: new OA\Items(
                                type: "object",
                                properties: [
                                    new OA\Property(property: "path", type: "string", format: "binary"),
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
                description: "Miembro actualizado exitosamente"
            ),
            new OA\Response(response: 404, description: "No encontrado"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'baseLang' => 'required|string|max:5',
            'name' => 'required|string|max:255',
            'status' => 'required|string|in:active,inactive',
            'userId' => 'nullable|string|exists:users,id',
            'image' => 'nullable', // Puede ser un archivo nuevo o un string de la ruta existente
            'specialization' => 'nullable|string',
            'description' => 'nullable|string',
            'biography' => 'nullable|string',
            'gallery' => 'nullable|array',
            'gallery.*.path' => 'nullable', // Puede ser un string (imagen existente) o un File (nueva imagen)
            'gallery.*.order' => 'numeric',
        ]);

        $baseLang = $request->input('baseLang');
        $configuredTargets = config('services.google_translate.targets', ['es', 'en']);
        $targetLanguages = array_values(array_diff($configuredTargets, [$baseLang]));

        try {
            // Check if team member exists
            $currentTeam = $this->repository->findById($id);
            if (!$currentTeam) {
                return response()->json(['error' => 'Team member not found'], 404);
            }

            // Handle main image update
            $mainImagePath = $currentTeam->image();
            if ($request->hasFile('image')) {
                $mainImagePath = $this->storeImage($request->file('image'), "teams/{$id}/avatar");
            }

            // --- Handle Gallery Files ---
            $galleryData = [];
            foreach ($request->input('gallery', []) as $index => $item) {
                $path = $item['path'] ?? null;
                if ($request->hasFile("gallery.{$index}.path")) {
                    $path = $this->storeImage($request->file("gallery.{$index}.path"), "teams/{$id}/gallery");
                }
                if ($path) {
                    $galleryData[] = [
                        'path' => $path,
                        'order' => $item['order'] ?? 0
                    ];
                }
            }

            $this->useCase->execute(
                $id,
                $request->input('userId'),
                null, // slug (keep existing or let system regenerate if logic allows)
                $baseLang,
                $request->input('name'),
                $request->input('status'),
                $mainImagePath,
                $request->input('specialization'),
                $request->input('description'),
                $request->input('biography'),
                $targetLanguages,
                $galleryData
            );

            return response()->json([
                'message' => 'Team member updated successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
