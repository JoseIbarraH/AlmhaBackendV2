<?php

namespace Src\Admin\Role\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Admin\Role\Application\GetAllPermissionsUseCase;

class GetPermissionsController extends Controller
{
    private GetAllPermissionsUseCase $useCase;

    public function __construct(GetAllPermissionsUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(): JsonResponse
    {
        $permissions = $this->useCase->execute();
        
        return response()->json([
            'data' => $permissions
        ], 200);
    }
}
