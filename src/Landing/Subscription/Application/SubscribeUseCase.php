<?php

declare(strict_types=1);

namespace Src\Landing\Subscription\Application;

use Illuminate\Support\Str;
use Src\Landing\Subscription\Domain\Contracts\SubscriberRepositoryContract;
use Src\Landing\Subscription\Domain\Entity\Subscriber;
use Src\Landing\Subscription\Domain\ValueObjects\SubscriberEmail;
use Src\Landing\Subscription\Domain\ValueObjects\SubscriberToken;
use Src\Landing\Subscription\Infrastructure\Jobs\SendToN8nJob;

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
            null // Pending verification
        );

        $this->repository->save($subscriber);

        // Dispatch Job synchronously in logic, asynchronously in infrastructure
        SendToN8nJob::dispatch($subscriber->email()->value(), $subscriber->token()->value());
    }
}
