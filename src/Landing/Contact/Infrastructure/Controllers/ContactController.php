<?php

declare(strict_types=1);

namespace Src\Landing\Contact\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Src\Landing\Contact\Infrastructure\Requests\ContactRequest;
use Src\Shared\Infrastructure\Http\ApiResponse;

final class ContactController
{
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
