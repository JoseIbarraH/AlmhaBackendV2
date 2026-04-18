<?php

declare(strict_types=1);

namespace Src\Admin\User\Domain\ValueObjects;

final class UserVerificationToken
{
    private ?string $value;

    public function __construct(?string $value)
    {
        $this->value = $value;
    }

    public function value(): ?string
    {
        return $this->value;
    }
}
