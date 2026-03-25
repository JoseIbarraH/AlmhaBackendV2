<?php

namespace Src\Admin\User\Domain\ValueObjects;
final class UserRememberToken
{
    private $value;

    /**
     * @param string|null $token
     */
    public function __construct(?string $token)
    {
        $this->value = $token;
    }

    public function value(): ?string
    {
        return $this->value;
    }
}
