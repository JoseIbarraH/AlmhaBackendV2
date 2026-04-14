<?php

declare(strict_types=1);

namespace Src\Admin\User\Domain\ValueObjects;

final class UserMainAdmin
{
    private bool $value;

    public function __construct(bool $value)
    {
        $this->value = $value;
    }

    public function value(): bool
    {
        return $this->value;
    }
}
