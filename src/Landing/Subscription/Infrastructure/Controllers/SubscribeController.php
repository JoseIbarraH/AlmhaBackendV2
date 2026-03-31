<?php

declare(strict_types=1);

namespace Src\Landing\Subscription\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Landing\Subscription\Application\SubscribeUseCase;

final class SubscribeController
{
    private SubscribeUseCase $useCase;

    public function __construct(SubscribeUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $this->useCase->execute($request->input('email'));

            return response()->json([
                'message' => 'Subscription pending. Please check your email.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to subscribe: ' . $e->getMessage(),
            ], 400);
        }
    }
}
