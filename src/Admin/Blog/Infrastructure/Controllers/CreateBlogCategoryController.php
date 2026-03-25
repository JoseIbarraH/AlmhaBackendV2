<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Blog\Application\CreateBlogCategoryUseCase;
use Exception;

final class CreateBlogCategoryController
{
    private CreateBlogCategoryUseCase $useCase;

    public function __construct(CreateBlogCategoryUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:255|unique:blog_categories,code',
            'baseLang' => 'required|string|max:5',
            'title' => 'required|string|max:255'
        ]);

        $baseLang = $request->input('baseLang');
        $configuredTargets = config('services.google_translate.targets', ['es', 'en']);
        $targetLanguages = array_values(array_diff($configuredTargets, [$baseLang]));

        try {
            $this->useCase->execute(
                $request->input('code'),
                $baseLang,
                $request->input('title'),
                $targetLanguages
            );

            return response()->json([
                'message' => 'Blog category created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
