<?php

namespace Src\Admin\User\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\User\Application\UpdateUserUseCase;
use Src\Admin\User\Domain\Exceptions\UserNotFoundException;

class UpdateUserController extends Controller
{
    private UpdateUserUseCase $useCase;

    public function __construct(UpdateUserUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'nullable|string|min:6',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $this->useCase->execute(
                $id,
                $request->input('name'),
                $request->input('email'),
                $request->input('password'),
                $request->input('is_active')
            );
            
            return response()->json([
                'message' => 'Usuario actualizado exitosamente'
            ], 200);
            
        } catch (UserNotFoundException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
