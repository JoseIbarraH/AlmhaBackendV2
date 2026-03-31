<?php

declare(strict_types=1);

namespace Src\Landing\Subscription\Domain\Entity;

use DateTime;
use Src\Landing\Subscription\Domain\ValueObjects\SubscriberEmail;
use Src\Landing\Subscription\Domain\ValueObjects\SubscriberToken;

final class Subscriber
{
    private string $id;
    private SubscriberEmail $email;
    private SubscriberToken $token;
    private ?DateTime $verifiedAt;

    public function __construct(
        string $id,
        SubscriberEmail $email,
        SubscriberToken $token,
        ?DateTime $verifiedAt = null
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->token = $token;
        $this->verifiedAt = $verifiedAt;
    }

    public function id(): string { return $this->id; }
    public function email(): SubscriberEmail { return $this->email; }
    public function token(): SubscriberToken { return $this->token; }
    public function verifiedAt(): ?DateTime { return $this->verifiedAt; }

    public function verify(): void
    {
        $this->verifiedAt = new DateTime();
    }
}
