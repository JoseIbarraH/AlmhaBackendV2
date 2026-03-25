<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Src\Admin\Blog\Application\UpdateBlogUseCase;
use Src\Admin\Blog\Domain\Contracts\BlogRepositoryContract;
use Exception;

final class UpdateBlogController
{
    private UpdateBlogUseCase $useCase;
    private BlogRepositoryContract $repository;

    public function __construct(UpdateBlogUseCase $useCase, BlogRepositoryContract $repository)
    {
        $this->useCase = $useCase;
        $this->repository = $repository;
    }

    public function __invoke(Request $request, int $id): JsonResponse
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
            $this->useCase->execute(
                $id,
                $request->input('categoryCode'),
                $baseLang,
                $request->input('title'),
                $request->input('content'),
                $targetLanguages,
                $request->input('userId') ? (int) $request->input('userId') : null,
                null, // image se maneja aparte
                $request->input('writer')
            );

            // Subir nueva imagen a MinIO si se envió
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                
                // Eliminar imágenes anteriores de la carpeta
                Storage::disk('s3')->deleteDirectory("blogs/{$id}/main_image");
                
                $path = "blogs/{$id}/main_image/{$file->getClientOriginalName()}";
                Storage::disk('s3')->put($path, file_get_contents($file->getRealPath()));
                
                $imageUrl = Storage::disk('s3')->url($path);
                $this->repository->updateImage($id, $imageUrl);
            }

            return response()->json([
                'message' => 'Blog updated successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
