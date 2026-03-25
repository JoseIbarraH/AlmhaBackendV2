<?php

namespace Src\Admin\User\Domain\ValueObjects;
final class UserEmailVerifiedDate
{
    private $value;

    /**
     * @param string|null $date
     */
    public function __construct(?string $date)
    {
        $this->value = $date;
    }

    public function value(): ?string
    {
        return $this->value;
    }
}
