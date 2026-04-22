<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

final class ApiResponse
{
    public static function success(mixed $data = null, string $message = '', int $status = 200): JsonResponse
    {
        $body = [];
        if ($data !== null) {
            $body['data'] = $data;
        }
        if ($message !== '') {
            $body['message'] = $message;
        }

        return response()->json($body, $status);
    }

    public static function created(mixed $data = null, string $message = ''): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    public static function error(string $error, string $message = '', int $status = 400, array $errors = []): JsonResponse
    {
        $body = ['error' => $error];
        if ($message !== '') {
            $body['message'] = $message;
        }
        if ($errors !== []) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status);
    }

    public static function paginated(array $result): JsonResponse
    {
        return response()->json([
            'data' => $result['items'],
            'meta' => $result['meta'],
        ], 200);
    }
}
