<?php

namespace Src\Admin\User\Domain\Entity;

use Src\Admin\User\Domain\ValueObjects\UserName;
use Src\Admin\User\Domain\ValueObjects\UserEmail;
use Src\Admin\User\Domain\ValueObjects\UserEmailVerifiedDate;
use Src\Admin\User\Domain\ValueObjects\UserPassword;
use Src\Admin\User\Domain\ValueObjects\UserRememberToken;
use Src\Admin\User\Domain\ValueObjects\UserStatus;
use Src\Admin\User\Domain\ValueObjects\UserId;

final class User
{
    private $id;
    private $name;
    private $email;
    private $emailVerifiedDate;
    private $password;
    private $rememberToken;
    private $status;


    public function __construct(
        UserName $name,
        UserEmail $email,
        UserEmailVerifiedDate $emailVerifiedDate,
        UserPassword $password,
        UserRememberToken $rememberToken,
        UserStatus $status,
        ?UserId $id = null
    )
    {
        $this->name = $name;
        $this->email = $email;
        $this->emailVerifiedDate = $emailVerifiedDate;
        $this->password = $password;
        $this->rememberToken = $rememberToken;
        $this->status = $status;
        $this->id = $id;
    }

    public function id(): ?UserId
    {
        return $this->id;
    }

    public function name(): UserName
    {
        return $this->name;
    }

    public function email(): UserEmail
    {
        return $this->email;
    }

    public function emailVerifiedDate(): UserEmailVerifiedDate
    {
        return $this->emailVerifiedDate;
    }

    public function password(): UserPassword
    {
        return $this->password;
    }

    public function rememberToken(): UserRememberToken
    {
        return $this->rememberToken;
    }

    public function status(): UserStatus
    {
        return $this->status;
    }

    public static function create(
        UserName $name,
        UserEmail $email,
        UserEmailVerifiedDate $emailVerifiedDate,
        UserPassword $password,
        UserRememberToken $rememberToken,
        UserStatus $status,
        ?UserId $id = null
    ): User
    {
        $user = new self($name, $email, $emailVerifiedDate, $password, $rememberToken, $status, $id);

        return $user;
    }
}
