<?php

namespace Src\Admin\Auth\Application;

use Src\Admin\Auth\Domain\Contracts\AuthenticatorContract;
use Src\Admin\Auth\Domain\ValueObjects\AuthToken;

class RefreshTokenUseCase
{
    private AuthenticatorContract $authenticator;

    public function __construct(AuthenticatorContract $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    public function execute(): AuthToken
    {
        return $this->authenticator->refresh();
    }
}
