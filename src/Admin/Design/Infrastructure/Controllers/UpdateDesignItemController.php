<?php

namespace Src\Admin\Design\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Design\Application\UpdateDesignItemUseCase;

class UpdateDesignItemController
{
    private UpdateDesignItemUseCase $useCase;

    public function __construct(UpdateDesignItemUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request, int $itemId): JsonResponse
    {
        $request->validate([
            'media_file' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,mp4,webm,ogg',
            'order' => 'nullable|integer',
            'translations' => 'nullable|string', // JSON string for translations
            'baseLang' => 'nullable|string|max:5',
        ]);

        $data = $request->only(['order']);
        
        if ($request->hasFile('media_file')) {
            $data['media_file'] = $request->file('media_file');
        }

        if ($request->has('translations')) {
            $data['translations'] = json_decode($request->input('translations'), true);
        }

        $baseLang = $request->input('baseLang', substr($request->header('Accept-Language', 'es'), 0, 2));
        $configuredTargets = config('services.google_translate.targets', ['es', 'en']);
        $targetLanguages = array_values(array_diff($configuredTargets, [$baseLang]));

        try {
            $item = $this->useCase->execute($itemId, $data, $baseLang, $targetLanguages);
            
            return response()->json([
                'success' => true,
                'message' => 'Design item updated successfully',
                'data' => $item
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
