<?php

namespace Src\Admin\User\Domain\ValueObjects;

use InvalidArgumentException;

final class UserStatus
{
    private bool $value;

    public function __construct(bool $isActive = true)
    {
        $this->value = $isActive;
    }

    public function value(): bool
    {
        return $this->value;
    }

    public function isActive(): bool
    {
        return $this->value;
    }
}
