<?php

namespace Src\Admin\Role\Domain\Exceptions;

use Exception;

class RoleAlreadyExistsException extends Exception
{
    public function __construct(string $roleName)
    {
        parent::__construct("El rol '{$roleName}' ya existe.", 409);
    }
}
