<?php

namespace Src\Admin\Auth\Infrastructure\Security;

use Illuminate\Support\Facades\Auth;
use Src\Admin\Auth\Domain\Contracts\AuthenticatorContract;
use Src\Admin\Auth\Domain\Exceptions\InvalidCredentialsException;
use Src\Admin\Auth\Domain\ValueObjects\AuthToken;

class JwtLaravelAuthenticator implements AuthenticatorContract
{
    /**
     * @param array $credentials
     * @return AuthToken
     * @throws InvalidCredentialsException
     */
    public function login(array $credentials): AuthToken
    {
        $token = Auth::guard('api')->attempt($credentials);

        if (!$token) {
            throw new InvalidCredentialsException();
        }

        return new AuthToken($token);
    }

    public function refresh(): AuthToken
    {
        $token = Auth::guard('api')->refresh();

        return new AuthToken($token);
    }
}
