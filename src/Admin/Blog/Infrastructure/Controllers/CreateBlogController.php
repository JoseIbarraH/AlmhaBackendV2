<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Src\Admin\Blog\Application\CreateBlogUseCase;
use Src\Admin\Blog\Domain\Contracts\BlogRepositoryContract;
use Exception;

final class CreateBlogController
{
    private CreateBlogUseCase $useCase;
    private BlogRepositoryContract $repository;

    public function __construct(CreateBlogUseCase $useCase, BlogRepositoryContract $repository)
    {
        $this->useCase = $useCase;
        $this->repository = $repository;
    }

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
                $file = $request->file('image');
                $path = "blogs/{$blogId}/main_image/{$file->getClientOriginalName()}";
                
                Storage::disk('s3')->put($path, file_get_contents($file->getRealPath()));
                
                $imageUrl = Storage::disk('s3')->url($path);
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
