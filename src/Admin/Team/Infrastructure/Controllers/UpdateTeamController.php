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
        summary: "Actualizar un miembro del equipo (patch-style)",
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
                    required: ["baseLang"],
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
            'name' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:active,inactive',
            'userId' => 'nullable|string|exists:users,id',
            'image' => 'nullable',
            'specialization' => 'nullable|string',
            'description' => 'nullable|string',
            'biography' => 'nullable|string',
            'gallery' => 'nullable|array',
            'gallery.*.path' => 'nullable',
            'gallery.*.order' => 'numeric',
        ]);

        $baseLang = $request->input('baseLang');
        $configuredTargets = config('services.google_translate.targets', ['es', 'en']);
        $targetLanguages = array_values(array_diff($configuredTargets, [$baseLang]));

        try {
            // Handle main image update
            $mainImagePath = null;
            if ($request->hasFile('image')) {
                $mainImagePath = $this->storeImage($request->file('image'), "teams/{$id}/avatar");
            }

            // --- Handle Gallery Files (only if gallery was sent) ---
            $galleryData = null;
            if ($request->has('gallery')) {
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
            }

            $this->useCase->execute(
                $id,
                $baseLang,
                $request->has('name') ? $request->input('name') : null,
                $request->has('status') ? $request->input('status') : null,
                $request->has('userId') ? $request->input('userId') : null,
                null, // slug
                $mainImagePath,
                $request->has('specialization') ? $request->input('specialization') : null,
                $request->has('description') ? $request->input('description') : null,
                $request->has('biography') ? $request->input('biography') : null,
                $targetLanguages,
                $galleryData,
                $request->has('specialization'),
                $request->has('description'),
                $request->has('biography')
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
