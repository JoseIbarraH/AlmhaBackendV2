<?php

namespace Src\Admin\Auth\Infrastructure;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class ResendVerificationController
{
    #[OA\Post(
        path: "/auth/email/resend",
        summary: "Reenviar correo de verificación",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Correo de verificación reenviado"),
            new OA\Response(response: 404, description: "Usuario no encontrado"),
            new OA\Response(response: 400, description: "El correo ya está verificado")
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json(['message' => 'No se encontró un usuario con ese correo.'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'El correo ya ha sido verificado.'], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Correo de verificación reenviado.'], 200);
    }
}
