<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Blog\Application\UpdateBlogUseCase;
use Exception;

final class UpdateBlogController
{
    private UpdateBlogUseCase $useCase;

    public function __construct(UpdateBlogUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'slug' => 'required|string|max:255|unique:blogs,slug,' . $id,
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
                $id,
                $request->input('slug'),
                $request->input('categoryCode'),
                $baseLang,
                $request->input('title'),
                $request->input('content'),
                $targetLanguages,
                $request->input('userId') ? (int) $request->input('userId') : null,
                $request->input('image'),
                $request->input('writer')
            );

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
