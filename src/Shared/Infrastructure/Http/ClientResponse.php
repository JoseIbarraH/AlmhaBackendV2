<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Http;

use Illuminate\Http\JsonResponse;

/**
 * Envelope used by public /api/client/* endpoints.
 * Frontend expects shape: { success: bool, message: string, data: T }.
 */
final class ClientResponse
{
    public static function success(mixed $data = null, string $message = '', int $status = 200, ?int $maxAgeSeconds = null): JsonResponse
    {
        $response = response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);

        if ($maxAgeSeconds !== null && $maxAgeSeconds > 0) {
            $response->headers->set(
                'Cache-Control',
                "public, max-age={$maxAgeSeconds}, stale-while-revalidate=" . ($maxAgeSeconds * 2)
            );
        }

        return $response;
    }

    public static function error(string $message, int $status = 400, array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'success' => false,
            'message' => $message,
            'data'    => null,
        ], $extra), $status);
    }
}
