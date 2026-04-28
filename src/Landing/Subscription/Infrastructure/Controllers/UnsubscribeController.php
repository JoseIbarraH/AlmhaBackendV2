<?php

declare(strict_types=1);

namespace Src\Landing\Subscription\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Src\Landing\Subscription\Infrastructure\Models\SubscriberEloquentModel;
use Src\Shared\Infrastructure\Http\ClientResponse;

final class UnsubscribeController
{
    #[OA\Get(
        path: "/api/client/subscribe/unsubscribe",
        summary: "Cancela la suscripción al newsletter usando el token del subscriber",
        description: "GET redirige al cliente con un toast. POST (RFC 8058 One-Click) responde JSON 200. Idempotente.",
        tags: ["Client / Subscription"],
        parameters: [
            new OA\Parameter(name: "token", in: "query", required: true, schema: new OA\Schema(type: "string")),
        ],
        responses: [
            new OA\Response(response: 302, description: "Redirige al cliente con ?subscription=unsubscribed"),
            new OA\Response(response: 200, description: "POST one-click OK"),
        ]
    )]
    public function __invoke(Request $request): JsonResponse|RedirectResponse
    {
        $token = (string) ($request->query('token') ?? $request->input('token') ?? '');
        $isOneClickPost = $request->isMethod('post');

        $status = 'invalid';
        if ($token !== '') {
            $subscriber = SubscriberEloquentModel::where('token', $token)->first();
            if ($subscriber) {
                if ($subscriber->unsubscribed_at === null) {
                    $subscriber->unsubscribed_at = now();
                    $subscriber->save();
                }
                $status = 'unsubscribed';
            } else {
                $status = 'failed';
            }
        }

        if ($isOneClickPost) {
            return $status === 'unsubscribed'
                ? ClientResponse::success(['status' => 'unsubscribed'], 'Unsubscribed.')
                : ClientResponse::error('Invalid unsubscribe token.', 410, ['status' => $status]);
        }

        $clientUrl = rtrim((string) config('app.client_url'), '/');
        $supported = (array) config('app.supported_locales', ['es', 'en', 'fr']);
        $defaultLang = (string) config('app.locale', 'es');
        $lang = (string) $request->query('lang', '');
        if (!in_array($lang, $supported, true)) {
            $lang = in_array($defaultLang, $supported, true) ? $defaultLang : ($supported[0] ?? 'es');
        }

        return redirect()->away("{$clientUrl}/{$lang}/?subscription={$status}");
    }
}
