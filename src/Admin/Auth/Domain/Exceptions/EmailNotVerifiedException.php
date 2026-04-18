<?php

namespace Src\Admin\Auth\Domain\Exceptions;

use Exception;

class EmailNotVerifiedException extends Exception
{
    protected $message = 'Tu dirección de correo electrónico no ha sido verificada.';
}
