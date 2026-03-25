<?php

namespace Src\Admin\Auth\Infrastructure;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\Auth\Application\LoginUseCase;
use Src\Admin\Auth\Domain\Exceptions\InvalidCredentialsException;

class LoginController extends Controller
{
    private LoginUseCase $loginUseCase;

    public function __construct(LoginUseCase $loginUseCase)
    {
        $this->loginUseCase = $loginUseCase;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $authToken = $this->loginUseCase->execute(
                $request->input('email'),
                $request->input('password')
            );

            return response()->json([
                'access_token' => $authToken->value(),
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl', 60) * 60
            ], 200);
            
        } catch (InvalidCredentialsException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 401);
        }
    }
}
