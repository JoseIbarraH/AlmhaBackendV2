<?php

namespace Src\Admin\Auth\Infrastructure;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\Auth\Application\LoginUseCase;
use Src\Admin\Auth\Domain\Exceptions\InvalidCredentialsException;

use OpenApi\Attributes as OA;

class LoginController extends Controller
{
    private LoginUseCase $loginUseCase;

    public function __construct(LoginUseCase $loginUseCase)
    {
        $this->loginUseCase = $loginUseCase;
    }

    #[OA\Post(
        path: "/auth/login",
        summary: "Iniciar sesión",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Login exitoso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "access_token", type: "string"),
                        new OA\Property(property: "token_type", type: "string", example: "bearer"),
                        new OA\Property(property: "expires_in", type: "integer", example: 3600)
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Credenciales inválidas"),
            new OA\Response(response: 403, description: "Correo no verificado")
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $rememberMe = $request->input('remember_me', false);
            $authToken = $this->loginUseCase->execute(
                $request->input('email'),
                $request->input('password'),
                $rememberMe
            );

            return response()->json([
                'access_token' => $authToken->value(),
                'token_type' => 'bearer',
                'expires_in' => $rememberMe ? 43200 * 60 : config('jwt.ttl', 60) * 60
            ], 200);
            
        } catch (\Src\Admin\Auth\Domain\Exceptions\EmailNotVerifiedException $e) {
            return response()->json([
                'error' => 'not_verified',
                'message' => $e->getMessage()
            ], 403);
        } catch (InvalidCredentialsException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 401);
        }
    }
}
