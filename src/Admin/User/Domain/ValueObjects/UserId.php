<?php

namespace Src\Admin\User\Domain\ValueObjects;
final class UserId
{
    private $value;

    /**
     * @param string $id
     * @throws \InvalidArgumentException
     */
    public function __construct(string $id)
    {
        $this->value = $id;
    }

    public function value(): string
    {
        return $this->value;
    }
}
