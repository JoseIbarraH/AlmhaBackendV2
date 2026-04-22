<?php

declare(strict_types=1);

namespace Src\Landing\Subscription\Infrastructure\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Src\Landing\Subscription\Application\ConfirmSubscriptionUseCase;

final class ConfirmSubscriptionController
{
    public function __construct(private readonly ConfirmSubscriptionUseCase $useCase)
    {
    }

    /**
     * GET /api/v1/subscribe/confirm?token=...
     *
     * Clicked from an email. Confirms the subscription and redirects the user
     * to the frontend with a status query param so the SPA can show a toast
     * or banner. Uses GET (not POST) because email clients always issue GET.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $token = (string) $request->query('token', '');

        if ($token === '') {
            return $this->redirect('invalid');
        }

        try {
            $this->useCase->execute($token);
            return $this->redirect('confirmed');
        } catch (\Throwable $e) {
            return $this->redirect('failed');
        }
    }

    private function redirect(string $status): RedirectResponse
    {
        $frontend = rtrim((string) config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:4321')), '/');
        return redirect()->away("{$frontend}/?subscription={$status}");
    }
}
