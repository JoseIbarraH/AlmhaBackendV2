<?php

namespace Src\Admin\User\Domain\ValueObjects;
final class UserName
{
    private $value;

    /**
     * @param string $name
     * @throws InvalidArgumentException
     */
    public function __construct(string $name)
    {
        $this->validate($name);
        $this->value = $name;
    }

    private function validate(string $name): void
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException(
                sprintf('<%s> does not allow the empty name: <%s>.', static::class, $name)
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
