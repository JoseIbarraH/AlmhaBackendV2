<?php

namespace Src\Admin\Role\Domain\Exceptions;

use Exception;

class RoleNotFoundException extends Exception
{
    public function __construct(string $roleName)
    {
        parent::__construct("El rol '{$roleName}' no fue encontrado.", 404);
    }
}
