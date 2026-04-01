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
        ]);

        $data = $request->only(['design_id', 'order']);
        
        if ($request->hasFile('media_file')) {
            $data['media_file'] = $request->file('media_file');
        }

        if ($request->has('translations')) {
            $data['translations'] = json_decode($request->input('translations'), true);
        }

        $item = $this->useCase->execute($data);

        return response()->json([
            'success' => true,
            'message' => 'Design item saved successfully',
            'data' => $item
        ], 201);
    }
}
