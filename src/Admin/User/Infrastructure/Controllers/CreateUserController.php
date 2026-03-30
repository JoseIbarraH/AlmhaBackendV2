<?php

declare(strict_types = 1);

namespace Src\Admin\User\Infrastructure\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Src\Admin\User\Application\CreateUserUseCase;
use Src\Admin\User\Application\GetUserByCriteriaUseCase;
use Src\Admin\User\Infrastructure\Repositories\EloquentUserRepository;

use OpenApi\Attributes as OA;

final class CreateUserController
{
    private $repository;

    public function __construct(EloquentUserRepository $repository)
    {
        $this->repository = $repository;
    }

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
                    new OA\Property(property: "role", type: "string", example: "blog_manager", description: "Nombre del rol a asignar")
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
        $userName = $request->input('name');
        $userEmail = $request->input('email');
        $userEmailVerifiedDate = null;
        $userPassword = Hash::make($request->input('password'));
        $userRememberToken = null;
        $isActive = $request->input('is_active', true);
        $roleName = $request->input('role');

        $createUserUseCase = new CreateUserUseCase($this->repository);
        $createUserUseCase->__invoke(
            $userName,
            $userEmail,
            $userEmailVerifiedDate,
            $userPassword,
            $userRememberToken,
            $isActive,
            $roleName
        );

        $getUserByCriteriaUseCase = new GetUserByCriteriaUseCase($this->repository);
        $matchedUsers = $getUserByCriteriaUseCase->execute(null, $userName, $userEmail);
        $newUser = count($matchedUsers) > 0 ? $matchedUsers[0] : null;

        return response()->json(new UserResource($newUser), 201);
    }
}
