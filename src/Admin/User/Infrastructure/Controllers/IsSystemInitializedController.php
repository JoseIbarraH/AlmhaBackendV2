<?php

declare(strict_types=1);

namespace Src\Admin\User\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Admin\User\Application\IsSystemInitializedUseCase;
use Src\Admin\User\Infrastructure\Repositories\EloquentUserRepository;
use OpenApi\Attributes as OA;

final class IsSystemInitializedController
{
    private EloquentUserRepository $repository;

    public function __construct(EloquentUserRepository $repository)
    {
        $this->repository = $repository;
    }

    #[OA\Get(
        path: "/setup/instance-status",
        summary: "Verificar si el sistema está inicializado",
        tags: ["Setup"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Estado de inicialización",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "initialized", type: "boolean", example: true)
                    ]
                )
            )
        ]
    )]
    public function __invoke(): JsonResponse
    {
        $useCase = new IsSystemInitializedUseCase($this->repository);
        $initialized = $useCase->execute();

        return response()->json([
            'initialized' => $initialized
        ]);
    }
}
