<?php

namespace Src\Admin\Design\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Design\Application\SaveDesignItemUseCase;

class SaveDesignItemController
{
    private SaveDesignItemUseCase $useCase;

    public function __construct(SaveDesignItemUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'design_id' => 'required|integer|exists:designs,id',
            'media_file' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,mp4,webm,ogg',
            'order' => 'nullable|integer',
            'translations' => 'nullable|string', // JSON string for translations
            'baseLang' => 'nullable|string|max:5',
        ]);

        $data = $request->only(['design_id', 'order']);
        
        if ($request->hasFile('media_file')) {
            $data['media_file'] = $request->file('media_file');
        }

        if ($request->has('translations')) {
            $decoded = json_decode($request->input('translations'), true);
            $data['translations'] = is_array($decoded) ? $decoded : null;
        }

        $baseLang = $request->input('baseLang', substr($request->header('Accept-Language', 'es'), 0, 2));
        $configuredTargets = config('services.google_translate.targets', ['es', 'en']);
        $targetLanguages = array_values(array_diff($configuredTargets, [$baseLang]));

        $item = $this->useCase->execute($data, $baseLang, $targetLanguages);

        return response()->json([
            'success' => true,
            'message' => 'Design item saved successfully',
            'data' => $item
        ], 201);
    }
}
