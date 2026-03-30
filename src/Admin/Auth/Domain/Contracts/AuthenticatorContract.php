<?php

namespace Src\Admin\Auth\Domain\Contracts;

use Src\Admin\Auth\Domain\ValueObjects\AuthToken;

interface AuthenticatorContract
{
    /**
     * @param array $credentials
     * @return AuthToken
     * @throws \Src\Admin\Auth\Domain\Exceptions\InvalidCredentialsException
     */
    public function login(array $credentials): AuthToken;

    /**
     * @return AuthToken
     */
    public function refresh(): AuthToken;
}
