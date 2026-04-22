<?php

declare(strict_types = 1);

namespace Src\Admin\User\Infrastructure\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Src\Admin\User\Application\CreateUserUseCase;
use Src\Admin\User\Application\GetUserByCriteriaUseCase;
use Src\Shared\Infrastructure\Http\ApiResponse;

use OpenApi\Attributes as OA;

final class CreateUserController
{
    public function __construct(
        private CreateUserUseCase $createUserUseCase,
        private GetUserByCriteriaUseCase $getUserByCriteriaUseCase,
    ) {}


    #[OA\Post(
        path: "/users",
        summary: "Crear un nuevo usuario",
        tags: ["User"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Juan Perez"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "juan@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123"),
                    new OA\Property(
                        property: "roles",
                        type: "array",
                        items: new OA\Items(type: "string"),
                        example: ["blog_manager", "audit_viewer"],
                        description: "Arreglo de nombres de roles a asignar"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Usuario creado exitosamente"
            ),
            new OA\Response(response: 422, description: "Error de validación")
        ]
    )]
    public function __invoke(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:4',
            'is_active' => 'nullable|boolean',
            'roles'    => 'nullable|array',
            'roles.*'  => 'string|exists:roles,name',
        ]);

        $userName = $request->input('name');
        $userEmail = $request->input('email');
        $userEmailVerifiedDate = null;
        $userPassword = Hash::make($request->input('password'));
        $userRememberToken = null;
        $isActive = $request->input('is_active', true);
        $roles = $request->input('roles', []);

        ($this->createUserUseCase)(
            $userName,
            $userEmail,
            $userEmailVerifiedDate,
            $userPassword,
            $userRememberToken,
            $isActive,
            $roles
        );

        $matchedUsers = $this->getUserByCriteriaUseCase->execute(null, $userName, $userEmail);
        $newUser = count($matchedUsers) > 0 ? $matchedUsers[0] : null;

        return ApiResponse::created(new UserResource($newUser));
    }
}
