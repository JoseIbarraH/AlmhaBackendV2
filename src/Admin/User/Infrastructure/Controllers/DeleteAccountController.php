<?php

declare(strict_types=1);

namespace Src\Admin\User\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Src\Admin\User\Application\DeleteAccountUseCase;
use OpenApi\Attributes as OA;

final class DeleteAccountController extends Controller
{
    private DeleteAccountUseCase $useCase;

    public function __construct(DeleteAccountUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Delete(
        path: "/profile",
        summary: "Eliminar la cuenta del usuario autenticado",
        tags: ["Profile"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Cuenta eliminada correctamente"),
            new OA\Response(response: 400, description: "Error de lógica (ej: cuenta principal)")
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $this->useCase->execute((string)$request->user()->id);

            return response()->json(['message' => 'Account deleted successfully'], 200);
        } catch (\LogicException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
