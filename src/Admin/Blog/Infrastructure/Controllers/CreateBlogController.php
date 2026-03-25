<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Blog\Application\CreateBlogUseCase;
use Exception;

final class CreateBlogController
{
    private CreateBlogUseCase $useCase;

    public function __construct(CreateBlogUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'slug' => 'required|string|max:255|unique:blogs,slug',
            'categoryCode' => 'required|string|exists:blog_categories,code',
            'baseLang' => 'required|string|max:5',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'userId' => 'nullable|integer|exists:users,id',
            'image' => 'nullable|string|max:255',
            'writer' => 'nullable|string|max:100'
        ]);

        $baseLang = $request->input('baseLang');
        $configuredTargets = config('services.google_translate.targets', ['es', 'en']);
        $targetLanguages = array_values(array_diff($configuredTargets, [$baseLang]));

        try {
            $this->useCase->execute(
                $request->input('slug'),
                $request->input('categoryCode'),
                $baseLang,
                $request->input('title'),
                $request->input('content'),
                $targetLanguages,
                'draft',
                $request->input('userId') ? (int) $request->input('userId') : null,
                $request->input('image'),
                $request->input('writer')
            );

            return response()->json([
                'message' => 'Blog created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
