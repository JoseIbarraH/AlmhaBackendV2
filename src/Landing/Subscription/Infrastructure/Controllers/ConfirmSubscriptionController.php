<?php

declare(strict_types=1);

namespace Src\Landing\Subscription\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Landing\Subscription\Application\ConfirmSubscriptionUseCase;

final class ConfirmSubscriptionController
{
    private ConfirmSubscriptionUseCase $useCase;

    public function __construct(ConfirmSubscriptionUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            $this->useCase->execute($request->input('token'));

            return response()->json([
                'message' => 'Subscription confirmed.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to confirm subscription: ' . $e->getMessage(),
            ], 400);
        }
    }
}
