<?php

declare(strict_types=1);

namespace Src\Landing\Subscription\Infrastructure\Repositories;

use Src\Landing\Subscription\Domain\Contracts\SubscriberRepositoryContract;
use Src\Landing\Subscription\Domain\Entity\Subscriber;
use Src\Landing\Subscription\Domain\ValueObjects\SubscriberEmail;
use Src\Landing\Subscription\Domain\ValueObjects\SubscriberToken;
use Src\Landing\Subscription\Infrastructure\Models\SubscriberEloquentModel;

final class EloquentSubscriberRepository implements SubscriberRepositoryContract
{
    private SubscriberEloquentModel $model;

    public function __construct(SubscriberEloquentModel $model)
    {
        $this->model = $model;
    }

    public function save(Subscriber $subscriber): void
    {
        $this->model->create([
            'id' => $subscriber->id(),
            'email' => $subscriber->email()->value(),
            'token' => $subscriber->token()->value(),
            'verified_at' => $subscriber->verifiedAt(),
        ]);
    }

    public function findByToken(SubscriberToken $token): ?Subscriber
    {
        $subscriber = $this->model->where('token', $token->value())->first();

        if (!$subscriber) {
            return null;
        }

        return new Subscriber(
            (string)$subscriber->id,
            new SubscriberEmail($subscriber->email),
            new SubscriberToken($subscriber->token),
            $subscriber->verified_at
        );
    }

    public function update(Subscriber $subscriber): void
    {
        $this->model->where('id', $subscriber->id())->update([
            'email' => $subscriber->email()->value(),
            'token' => $subscriber->token()->value(),
            'verified_at' => $subscriber->verifiedAt(),
        ]);
    }
}
