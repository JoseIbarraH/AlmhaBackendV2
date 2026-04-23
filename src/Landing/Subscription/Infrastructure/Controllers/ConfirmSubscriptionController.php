<?php

declare(strict_types=1);

namespace Src\Landing\Subscription\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Landing\Subscription\Application\ConfirmSubscriptionUseCase;
use Src\Shared\Infrastructure\Http\ClientResponse;

final class ConfirmSubscriptionController
{
    public function __construct(private readonly ConfirmSubscriptionUseCase $useCase)
    {
    }

    /**
     * GET /api/client/subscribe/confirm?token=...
     *
     * Called by the public frontend confirmation page (the user clicks a link
     * in an email that lands on the SSR page; the page calls this endpoint
     * server-side and renders the result). Returns JSON — UI is the
     * frontend's responsibility.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $token = (string) $request->query('token', '');

        if ($token === '') {
            return ClientResponse::error('Token is required.', 400, ['status' => 'invalid']);
        }

        try {
            $this->useCase->execute($token);
            return ClientResponse::success(['status' => 'confirmed'], 'Subscription confirmed.');
        } catch (\Throwable $e) {
            return ClientResponse::error('Could not confirm subscription.', 410, ['status' => 'failed']);
        }
    }
}
