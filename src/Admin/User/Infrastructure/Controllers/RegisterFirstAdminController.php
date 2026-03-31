<?php

declare(strict_types=1);

namespace Src\Admin\User\Infrastructure\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Src\Admin\User\Application\GetUserByCriteriaUseCase;
use Src\Admin\User\Application\RegisterFirstAdminUseCase;
use Src\Admin\User\Infrastructure\Repositories\EloquentUserRepository;
use OpenApi\Attributes as OA;

final class RegisterFirstAdminController
{
    private EloquentUserRepository $repository;

    public function __construct(EloquentUserRepository $repository)
    {
        $this->repository = $repository;
    }

    #[OA\Post(
        path: "/setup/instance-bootstrap",
        summary: "Registrar el primer usuario administrador",
        tags: ["Setup"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Admin Name"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "admin@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Administrador creado exitosamente"),
            new OA\Response(response: 400, description: "Error de lógica (ya inicializado)"),
            new OA\Response(response: 422, description: "Error de validación")
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8'
        ]);

        try {
            $useCase = new RegisterFirstAdminUseCase($this->repository);
            $useCase->execute(
                $request->input('name'),
                $request->input('email'),
                Hash::make($request->input('password'))
            );

            $getUserByCriteriaUseCase = new GetUserByCriteriaUseCase($this->repository);
            $matchedUsers = $getUserByCriteriaUseCase->execute(null, $request->input('name'), $request->input('email'));
            $newUser = count($matchedUsers) > 0 ? $matchedUsers[0] : null;

            return response()->json(new UserResource($newUser), 201);
        } catch (\LogicException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred.'], 500);
        }
    }
}
