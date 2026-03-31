<?php

declare(strict_types=1);

namespace Src\Landing\Subscription\Domain\Contracts;

use Src\Landing\Subscription\Domain\Entity\Subscriber;
use Src\Landing\Subscription\Domain\ValueObjects\SubscriberToken;

interface SubscriberRepositoryContract
{
    public function save(Subscriber $subscriber): void;
    public function findByToken(SubscriberToken $token): ?Subscriber;
    public function update(Subscriber $subscriber): void;
}
