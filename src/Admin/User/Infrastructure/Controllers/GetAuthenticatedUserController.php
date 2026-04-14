<?php

declare(strict_types=1);

namespace Src\Admin\User\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use OpenApi\Attributes as OA;
use Src\Admin\User\Domain\Contracts\UserRepositoryContract;
use Src\Admin\User\Domain\ValueObjects\UserId;

final class GetAuthenticatedUserController extends Controller
{
    private UserRepositoryContract $repository;

    public function __construct(UserRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    #[OA\Get(
        path: "/user",
        summary: "Obtener usuario autenticado",
        tags: ["User"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Datos del usuario",
                content: new OA\JsonContent(type: "object")
            ),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = new UserId((string)$request->user()->id);
        $user = $this->repository->findById($userId);

        return response()->json(new UserResource($user), 200);
    }
}
