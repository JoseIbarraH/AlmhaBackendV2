<?php

namespace Src\Admin\Auth\Infrastructure\Security;

use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;
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
    public function login(array $credentials, bool $rememberMe = false): AuthToken
    {
        if ($rememberMe) {
            // Set TTL to 30 days (43200 minutes)
            config(['jwt.ttl' => 43200]);
        }

        /** @var JWTGuard $guard */
        $guard = Auth::guard('api');
        $token = $guard->attempt($credentials);

        if (!$token) {
            throw new InvalidCredentialsException();
        }

        $user = $guard->user();
        if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail()) {
            throw new \Src\Admin\Auth\Domain\Exceptions\EmailNotVerifiedException();
        }

        return new AuthToken($token);
    }

    public function refresh(): AuthToken
    {
        /** @var JWTGuard $guard */
        $guard = Auth::guard('api');
        $token = $guard->refresh();

        return new AuthToken($token);
    }
}
