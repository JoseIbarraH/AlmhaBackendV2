<?php

namespace Src\Admin\User\Domain\ValueObjects;
final class UserId
{
    private $value;

    /**
     * @param int $id
     * @throws InvalidArgumentException
     */
    public function __construct(int $id)
    {
        $this->value = $id;
    }

    public function value(): int
    {
        return $this->value;
    }
}
