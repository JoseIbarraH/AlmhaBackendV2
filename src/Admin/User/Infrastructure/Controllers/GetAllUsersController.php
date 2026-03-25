<?php

namespace Src\Admin\User\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Admin\User\Application\GetAllUsersUseCase;

class GetAllUsersController extends Controller
{
    private GetAllUsersUseCase $useCase;

    public function __construct(GetAllUsersUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => $this->useCase->execute()
        ], 200);
    }
}
