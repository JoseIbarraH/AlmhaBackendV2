<?php

namespace Src\Admin\Auth\Domain\Exceptions;

use Exception;

class InvalidCredentialsException extends Exception
{
    public function __construct(string $message = "Las credenciales proporcionadas son inválidas.")
    {
        parent::__construct($message, 401);
    }
}
