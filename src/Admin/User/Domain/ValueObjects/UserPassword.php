<?php

namespace Src\Admin\User\Domain\ValueObjects;
final class UserPassword
{
    private $value;

    /**
     * @param string $password
     * @throws InvalidArgumentException
     */
    public function __construct(string $password)
    {
        $this->validate($password);
        $this->value = $password;
    }

    private function validate(string $password): void
    {
        if (strlen(trim($password)) < 4) {
            throw new \InvalidArgumentException(
                sprintf('<%s> password must be at least 4 characters.', static::class)
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
