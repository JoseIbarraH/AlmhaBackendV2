<?php

namespace Src\Admin\Role\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\Role\Application\CreateRoleUseCase;
use Src\Admin\Role\Domain\Exceptions\RoleAlreadyExistsException;

class CreateRoleController extends Controller
{
    private CreateRoleUseCase $useCase;

    public function __construct(CreateRoleUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {
            $this->useCase->execute($request->input('name'));
            
            return response()->json([
                'message' => 'Rol creado correctamente'
            ], 201);
            
        } catch (RoleAlreadyExistsException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 409);
        }
    }
}
