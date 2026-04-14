<?php

namespace Src\Admin\User\Domain\Entity;

use Src\Admin\User\Domain\ValueObjects\UserName;
use Src\Admin\User\Domain\ValueObjects\UserEmail;
use Src\Admin\User\Domain\ValueObjects\UserEmailVerifiedDate;
use Src\Admin\User\Domain\ValueObjects\UserPassword;
use Src\Admin\User\Domain\ValueObjects\UserRememberToken;
use Src\Admin\User\Domain\ValueObjects\UserStatus;
use Src\Admin\User\Domain\ValueObjects\UserId;
use Src\Admin\User\Domain\ValueObjects\UserMainAdmin;

final class User
{
    private $id;
    private $name;
    private $email;
    private $emailVerifiedDate;
    private $password;
    private $rememberToken;
    private $status;
    private $isMainAdmin;
    private $roles;


    public function __construct(
        UserName $name,
        UserEmail $email,
        UserEmailVerifiedDate $emailVerifiedDate,
        UserPassword $password,
        UserRememberToken $rememberToken,
        UserStatus $status,
        UserMainAdmin $isMainAdmin,
        array $roles = [],
        ?UserId $id = null
    )
    {
        $this->name = $name;
        $this->email = $email;
        $this->emailVerifiedDate = $emailVerifiedDate;
        $this->password = $password;
        $this->rememberToken = $rememberToken;
        $this->status = $status;
        $this->isMainAdmin = $isMainAdmin;
        $this->roles = $roles;
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

    public function isMainAdmin(): UserMainAdmin
    {
        return $this->isMainAdmin;
    }

    public function roles(): array
    {
        return $this->roles;
    }

    public static function create(
        UserName $name,
        UserEmail $email,
        UserEmailVerifiedDate $emailVerifiedDate,
        UserPassword $password,
        UserRememberToken $rememberToken,
        UserStatus $status,
        UserMainAdmin $isMainAdmin,
        array $roles = [],
        ?UserId $id = null
    ): User
    {
        $user = new self($name, $email, $emailVerifiedDate, $password, $rememberToken, $status, $isMainAdmin, $roles, $id);

        return $user;
    }
}
