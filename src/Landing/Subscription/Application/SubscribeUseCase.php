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

    public function execute(string $email): void
    {
        $subscriber = new Subscriber(
            (string) Str::uuid(),
            new SubscriberEmail($email),
            new SubscriberToken(Str::random(60)),
            null
        );

        $this->repository->save($subscriber);

        $clientUrl = rtrim((string) config('app.client_url'), '/');
        $defaultLang = (string) config('app.locale', 'es');
        $confirmationUrl = "{$clientUrl}/{$defaultLang}/subscribe/confirm?token=" . urlencode($subscriber->token()->value());

        Mail::to($subscriber->email()->value())->queue(new SubscriptionConfirmationEmail($confirmationUrl));
    }
}
