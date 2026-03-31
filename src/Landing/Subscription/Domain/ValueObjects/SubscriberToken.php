<?php

declare(strict_types=1);

namespace Src\Landing\Subscription\Domain\ValueObjects;

final class SubscriberToken
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }
}
