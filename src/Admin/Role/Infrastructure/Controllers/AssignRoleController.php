<?php

namespace Src\Admin\Role\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\Role\Application\AssignRoleToUserUseCase;
use Src\Admin\Role\Domain\Exceptions\RoleNotFoundException;

use OpenApi\Attributes as OA;

class AssignRoleController extends Controller
{
    private AssignRoleToUserUseCase $useCase;

    public function __construct(AssignRoleToUserUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Post(
        path: "/roles/assign",
        summary: "Asignar un rol a un usuario",
        tags: ["Role"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["user_id", "role_name"],
                properties: [
                    new OA\Property(property: "user_id", type: "string", example: "uuid-del-usuario"),
                    new OA\Property(property: "role_name", type: "string", example: "ADMIN")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Rol asignado exitosamente"
            ),
            new OA\Response(response: 404, description: "Rol no encontrado"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
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
