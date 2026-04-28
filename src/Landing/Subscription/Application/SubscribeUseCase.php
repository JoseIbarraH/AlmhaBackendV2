<?php

declare(strict_types=1);

namespace Src\Landing\Subscription\Application;

use App\Mail\SubscriptionConfirmationEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Src\Landing\Subscription\Domain\Contracts\SubscriberRepositoryContract;
use Src\Landing\Subscription\Domain\Entity\Subscriber;
use Src\Landing\Subscription\Domain\ValueObjects\SubscriberEmail;
use Src\Landing\Subscription\Domain\ValueObjects\SubscriberToken;

final class SubscribeUseCase
{
    private SubscriberRepositoryContract $repository;

    public function __construct(SubscriberRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $email, ?string $locale = null): void
    {
        $subscriber = new Subscriber(
            (string) Str::uuid(),
            new SubscriberEmail($email),
            new SubscriberToken(Str::random(60)),
            null
        );

        $this->repository->save($subscriber);

        $clientUrl = rtrim((string) config('app.client_url'), '/');
        $lang = $this->resolveLocale($locale);
        $confirmationUrl = "{$clientUrl}/{$lang}/subscribe/confirm?token=" . urlencode($subscriber->token()->value());

        Mail::to($subscriber->email()->value())->queue(new SubscriptionConfirmationEmail($confirmationUrl));
    }

    private function resolveLocale(?string $locale): string
    {
        $supported = (array) config('app.supported_locales', ['es', 'en', 'fr']);
        if ($locale !== null && in_array($locale, $supported, true)) {
            return $locale;
        }
        return (string) config('app.locale', 'es');
    }
}
