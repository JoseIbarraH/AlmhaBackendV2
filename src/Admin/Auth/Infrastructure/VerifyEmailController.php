<?php

namespace Src\Admin\Auth\Infrastructure;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\User\Domain\Contracts\UserRepositoryContract;
use OpenApi\Attributes as OA;

final class VerifyEmailController
{
    private UserRepositoryContract $repository;

    public function __construct(UserRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    #[OA\Get(
        path: "/auth/email/verify/{token}",
        summary: "Verificar correo electrónico mediante token",
        tags: ["Auth"],
        parameters: [
            new OA\Parameter(name: "token", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Correo verificado exitosamente"),
            new OA\Response(response: 400, description: "Token inválido o expirado")
        ]
    )]
    public function __invoke(Request $request, string $token): JsonResponse
    {
        $user = $this->repository->findByToken($token);

        if (!$user) {
            return response()->json(['error' => 'not_found', 'message' => 'Token de verificación inválido.'], 404);
        }

        if ($user->emailVerifiedDate()->value() !== null) {
            $user->clearVerificationToken();
            $this->repository->update($user);
            return response()->json(['message' => 'El correo ya ha sido verificado.'], 200);
        }

        $user->verify();
        
        $this->repository->update($user);

        // Disparar evento de Laravel para cualquier otro listener (opcional)
        $eloquentUser = \App\Models\User::find($user->id()->value());
        if ($eloquentUser) {
            event(new \Illuminate\Auth\Events\Verified($eloquentUser));
        }

        return response()->json(['message' => 'Correo verificado exitosamente.'], 200);
    }
}
