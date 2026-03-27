<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Procedure\Application\UpdateProcedureCategoryUseCase;
use Exception;

final class UpdateProcedureCategoryController
{
    private UpdateProcedureCategoryUseCase $useCase;

    public function __construct(UpdateProcedureCategoryUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:255|unique:procedure_categories,code,' . $id,
            'baseLang' => 'required|string|max:5',
            'title' => 'required|string|max:255',
        ]);

        $baseLang = $request->input('baseLang');
        $configuredTargets = config('services.google_translate.targets', ['es', 'en']);
        $targetLanguages = array_values(array_diff($configuredTargets, [$baseLang]));

        try {
            $this->useCase->execute(
                $id,
                $request->input('code'),
                $baseLang,
                $request->input('title'),
                $targetLanguages
            );

            return response()->json([
                'message' => 'Procedure category updated successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
