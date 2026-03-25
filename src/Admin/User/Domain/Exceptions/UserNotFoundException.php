<?php

namespace Src\Admin\User\Domain\Exceptions;

use Exception;

class UserNotFoundException extends Exception
{
    public function __construct(string $id)
    {
        parent::__construct("El usuario con ID '{$id}' no fue encontrado.", 404);
    }
}
