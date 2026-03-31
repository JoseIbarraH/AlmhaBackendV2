<?php

declare(strict_types=1);

namespace Src\Landing\Subscription\Domain\ValueObjects;

use InvalidArgumentException;

final class SubscriberEmail
{
    private string $value;

    public function __construct(string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(sprintf('<%s> does not allow the value <%s>.', static::class, $value));
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }
}
