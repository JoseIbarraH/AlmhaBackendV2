<?php

declare(strict_types=1);

namespace Src\Admin\User\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\User\Application\GetUserByCriteriaUseCase;
use Exception;

final class GetUserByCriteriaController
{
    private GetUserByCriteriaUseCase $useCase;

    public function __construct(GetUserByCriteriaUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'term' => 'nullable|string',
            'name' => 'nullable|string',
            'email' => 'nullable|string',
        ]);

        try {
            $users = $this->useCase->execute(
                $request->query('term'),
                $request->query('name'),
                $request->query('email')
            );
            
            $data = array_map(function($user) {
                return [
                    'id' => $user->id() ? $user->id()->value() : null,
                    'name' => $user->name()->value(),
                    'email' => $user->email()->value(),
                    'is_active' => $user->status()->value(),
                ];
            }, $users);

            return response()->json([
                'data' => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
