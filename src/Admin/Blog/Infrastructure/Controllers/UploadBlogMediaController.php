<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\Blog\Application\UploadBlogMediaUseCase;

final class UploadBlogMediaController extends Controller
{
    private UploadBlogMediaUseCase $useCase;

    public function __construct(UploadBlogMediaUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,svg,mp4,webm,mov|max:51200', // 50MB max
        ]);

        $url = $this->useCase->execute($request->file('file'));

        return response()->json([
            'success' => true,
            'url' => $url
        ], 201);
    }
}
