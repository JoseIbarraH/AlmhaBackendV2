<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Blog\Application\CreateBlogUseCase;
use Src\Admin\Blog\Domain\Contracts\BlogRepositoryContract;
use Src\Shared\Infrastructure\Traits\StoresImages;
use Exception;

use OpenApi\Attributes as OA;

final class CreateBlogController
{
    use StoresImages;

    private CreateBlogUseCase $useCase;
    private BlogRepositoryContract $repository;

    public function __construct(CreateBlogUseCase $useCase, BlogRepositoryContract $repository)
    {
        $this->useCase = $useCase;
        $this->repository = $repository;
    }

    #[OA\Post(
        path: "/blogs",
        summary: "Crear una nueva entrada de blog",
        tags: ["Blog"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["categoryCode", "baseLang", "title"],
                    properties: [
                        new OA\Property(property: "categoryCode", type: "string", example: "NOTICIAS"),
                        new OA\Property(property: "baseLang", type: "string", example: "es"),
                        new OA\Property(property: "title", type: "string", example: "Mi nuevo artículo"),
                        new OA\Property(property: "content", type: "string"),
                        new OA\Property(property: "userId", type: "integer"),
                        new OA\Property(property: "writer", type: "string"),
                        new OA\Property(property: "image", type: "string", format: "binary", description: "Imagen principal del blog")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Blog creado exitosamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(property: "blogId", type: "integer")
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
            'categoryCode' => 'required|string|exists:blog_categories,code',
            'baseLang' => 'required|string|max:5',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'userId' => 'nullable|integer|exists:users,id',
            'image' => 'nullable|file|image|max:5120', // max 5MB
            'writer' => 'nullable|string|max:100'
        ]);

        $baseLang = $request->input('baseLang');
        $configuredTargets = config('services.google_translate.targets', ['es', 'en']);
        $targetLanguages = array_values(array_diff($configuredTargets, [$baseLang]));

        try {
            $blogId = $this->useCase->execute(
                $request->input('categoryCode'),
                $baseLang,
                $request->input('title'),
                $request->input('content'),
                $targetLanguages,
                'draft',
                $request->input('userId') ? (int) $request->input('userId') : null,
                null, // image se sube después de crear el blog
                $request->input('writer')
            );

            // Subir imagen a MinIO si se envió
            if ($request->hasFile('image')) {
                $imageUrl = $this->storeImage(
                    $request->file('image'),
                    "blogs/{$blogId}/main_image"
                );
                $this->repository->updateImage($blogId, $imageUrl);
            }

            return response()->json([
                'message' => 'Blog created successfully',
                'blogId' => $blogId,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
