<?php

declare(strict_types=1);

namespace Src\Landing\Contact\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;
use Src\Landing\Contact\Infrastructure\Requests\ContactRequest;
use Src\Shared\Infrastructure\Http\ApiResponse;

final class ContactController
{
    #[OA\Post(
        path: "/api/v1/contact",
        summary: "Recibe formulario de contacto y lo reenvía a n8n",
        description: "Rate-limited a 5 req/min por IP. Valida server-side antes de reenviar.",
        tags: ["Landing / Webhooks"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "phone", "message"],
                properties: [
                    new OA\Property(property: "name", type: "string", maxLength: 100),
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "countryCode", type: "string", example: "+57"),
                    new OA\Property(property: "phone", type: "string", example: "300 123 4567"),
                    new OA\Property(property: "procedure", type: "string", nullable: true),
                    new OA\Property(property: "message", type: "string", maxLength: 2000),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Mensaje reenviado a n8n"),
            new OA\Response(response: 422, description: "Error de validación"),
            new OA\Response(response: 429, description: "Rate limit excedido"),
            new OA\Response(response: 502, description: "Fallo al reenviar al webhook"),
            new OA\Response(response: 503, description: "Webhook no configurado"),
        ]
    )]
    public function __invoke(ContactRequest $request): JsonResponse
    {
        $webhookUrl = config('services.n8n.contact_webhook_url');
        $authToken  = config('services.n8n.auth_token');

        if (!$webhookUrl) {
            Log::error('N8N_CONTACT_WEBHOOK_URL not configured.');
            return ApiResponse::error(
                'misconfigured',
                'El servicio de contacto no está disponible en este momento.',
                503
            );
        }

        $payload = array_merge($request->validated(), [
            'event'     => 'contact_form_submitted',
            'ip'        => $request->ip(),
            'userAgent' => (string) $request->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);

        try {
            $response = Http::timeout(10)
                ->withHeaders(array_filter([
                    'X-Auth-Token' => $authToken,
                    'Accept'       => 'application/json',
                ]))
                ->post($webhookUrl, $payload);

            if ($response->failed()) {
                Log::warning('Contact webhook failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return ApiResponse::error(
                    'forward_failed',
                    'No se pudo enviar el mensaje. Por favor intenta de nuevo.',
                    502
                );
            }
        } catch (\Throwable $e) {
            Log::error('Contact webhook exception: ' . $e->getMessage());
            return ApiResponse::error(
                'forward_failed',
                'Error temporal de conexión. Por favor intenta de nuevo.',
                502
            );
        }

        return ApiResponse::success(
            null,
            'Mensaje enviado con éxito. Nuestro equipo se pondrá en contacto pronto.',
            200
        );
    }
}
