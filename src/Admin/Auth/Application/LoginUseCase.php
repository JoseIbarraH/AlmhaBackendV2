<?php

namespace Src\Admin\Auth\Application;

use Src\Admin\Auth\Domain\Contracts\AuthenticatorContract;
use Src\Admin\Auth\Domain\Exceptions\InvalidCredentialsException;
use Src\Admin\Auth\Domain\ValueObjects\AuthToken;

class LoginUseCase
{
    private AuthenticatorContract $authenticator;

    public function __construct(AuthenticatorContract $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    /**
     * @param string $email
     * @param string $password
     * @return AuthToken
     * @throws InvalidCredentialsException
     */
    public function execute(string $email, string $password, bool $rememberMe = false): AuthToken
    {
        return $this->authenticator->login([
            'email' => $email,
            'password' => $password
        ], $rememberMe);
    }
}
