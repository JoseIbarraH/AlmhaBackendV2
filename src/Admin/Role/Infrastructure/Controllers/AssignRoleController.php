<?php

namespace Src\Admin\Role\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\Role\Application\AssignRoleToUserUseCase;
use Src\Admin\Role\Domain\Exceptions\RoleNotFoundException;

class AssignRoleController extends Controller
{
    private AssignRoleToUserUseCase $useCase;

    public function __construct(AssignRoleToUserUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required',
            'role_name' => 'required|string|max:255'
        ]);

        try {
            $this->useCase->execute(
                $request->input('user_id'), 
                $request->input('role_name')
            );
            
            return response()->json([
                'message' => 'Rol asignado correctamente'
            ], 200);
            
        } catch (RoleNotFoundException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
