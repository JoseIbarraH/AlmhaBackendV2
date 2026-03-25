<?php

namespace Src\Admin\Role\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Admin\Role\Application\GetAllRolesUseCase;

class GetRolesController extends Controller
{
    private GetAllRolesUseCase $useCase;

    public function __construct(GetAllRolesUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(): JsonResponse
    {
        $roles = $this->useCase->execute();
        
        return response()->json([
            'data' => $roles
        ], 200);
    }
}
