<?php

declare(strict_types=1);

namespace Src\Landing\Chat\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;
use Src\Landing\Chat\Infrastructure\Requests\ChatRequest;
use Src\Shared\Infrastructure\Http\ApiResponse;

final class ChatController
{
    #[OA\Post(
        path: "/api/v1/chat",
        summary: "Proxy del chat hacia n8n",
        description: "Rate-limited a 30 req/min por IP. Recibe el mensaje, lo reenvía a n8n y devuelve la respuesta del bot.",
        tags: ["Landing / Webhooks"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["chatInput", "sessionId"],
                properties: [
                    new OA\Property(property: "chatInput", type: "string", maxLength: 1500),
                    new OA\Property(property: "sessionId", type: "string", description: "Alfanumérico, generado en el cliente"),
                    new OA\Property(property: "action", type: "string", enum: ["sendMessage"], default: "sendMessage"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Respuesta de n8n (contiene output/text/response)"),
            new OA\Response(response: 422, description: "Error de validación"),
            new OA\Response(response: 429, description: "Rate limit excedido"),
            new OA\Response(response: 502, description: "Fallo al conectar con n8n"),
        ]
    )]
    public function __invoke(ChatRequest $request): JsonResponse
    {
        $webhookUrl = config('services.n8n.chat_webhook_url');
        $authToken  = config('services.n8n.auth_token');

        if (!$webhookUrl) {
            Log::error('N8N_CHAT_WEBHOOK_URL not configured.');
            return ApiResponse::error(
                'misconfigured',
                'El chat no está disponible en este momento.',
                503
            );
        }

        $payload = [
            'action'    => $request->input('action', 'sendMessage'),
            'chatInput' => $request->input('chatInput'),
            'sessionId' => $request->input('sessionId'),
        ];

        try {
            $response = Http::timeout(30)
                ->withHeaders(array_filter([
                    'X-Auth-Token' => $authToken,
                    'Accept'       => 'application/json',
                ]))
                ->post($webhookUrl, $payload);

            if ($response->failed()) {
                Log::warning('Chat webhook failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return ApiResponse::error(
                    'chat_failed',
                    'No se pudo procesar tu mensaje. Intenta de nuevo.',
                    502
                );
            }

            return response()->json($response->json(), 200);
        } catch (\Throwable $e) {
            Log::error('Chat webhook exception: ' . $e->getMessage());
            return ApiResponse::error(
                'chat_failed',
                'Error de conexión con el chat. Intenta de nuevo.',
                502
            );
        }
    }
}
