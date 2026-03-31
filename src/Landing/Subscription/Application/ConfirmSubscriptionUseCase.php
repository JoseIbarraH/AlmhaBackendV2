<?php

declare(strict_types=1);

namespace Src\Landing\Subscription\Application;

use Src\Landing\Subscription\Domain\Contracts\SubscriberRepositoryContract;
use Src\Landing\Subscription\Domain\ValueObjects\SubscriberToken;

final class ConfirmSubscriptionUseCase
{
    private SubscriberRepositoryContract $repository;

    public function __construct(SubscriberRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $token): void
    {
        $subscriber = $this->repository->findByToken(new SubscriberToken($token));

        if (!$subscriber) {
            throw new \RuntimeException('Subscription token not found.');
        }

        $subscriber->verify();

        $this->repository->update($subscriber);
    }
}
