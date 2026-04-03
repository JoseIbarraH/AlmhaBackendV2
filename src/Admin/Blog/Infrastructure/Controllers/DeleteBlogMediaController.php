<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\Blog\Application\DeleteBlogMediaUseCase;

final class DeleteBlogMediaController extends Controller
{
    private DeleteBlogMediaUseCase $useCase;

    public function __construct(DeleteBlogMediaUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|string',
        ]);

        $this->useCase->execute($request->input('url'));

        return response()->json([
            'success' => true
        ], 200);
    }
}
