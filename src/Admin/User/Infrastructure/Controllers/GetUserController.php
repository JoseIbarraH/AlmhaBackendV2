<?php

namespace Src\Admin\User\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Admin\User\Application\GetUserUseCase;
use Src\Admin\User\Domain\Exceptions\UserNotFoundException;

class GetUserController extends Controller
{
    private GetUserUseCase $useCase;

    public function __construct(GetUserUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $user = $this->useCase->execute($id);

            return response()->json([
                'data' => [
                    'name' => $user->name()->value(),
                    'email' => $user->email()->value(),
                ]
            ], 200);
            
        } catch (UserNotFoundException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
