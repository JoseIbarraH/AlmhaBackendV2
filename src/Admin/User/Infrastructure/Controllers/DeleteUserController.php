<?php

namespace Src\Admin\User\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Admin\User\Application\DeleteUserUseCase;
use Src\Admin\User\Domain\Exceptions\UserNotFoundException;

class DeleteUserController extends Controller
{
    private DeleteUserUseCase $useCase;

    public function __construct(DeleteUserUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->useCase->execute($id);

            return response()->json([
                'message' => 'Usuario eliminado correctamente'
            ], 200);
            
        } catch (UserNotFoundException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
