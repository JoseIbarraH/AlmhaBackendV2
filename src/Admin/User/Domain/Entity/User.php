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
use Src\Admin\User\Domain\ValueObjects\UserVerificationToken;

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
    private $verificationToken;
    private $roles;


    public function __construct(
        UserName $name,
        UserEmail $email,
        UserEmailVerifiedDate $emailVerifiedDate,
        UserPassword $password,
        UserRememberToken $rememberToken,
        UserStatus $status,
        UserMainAdmin $isMainAdmin,
        UserVerificationToken $verificationToken,
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
        $this->verificationToken = $verificationToken;
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

    public function verificationToken(): UserVerificationToken
    {
        return $this->verificationToken;
    }

    public function verify(): void
    {
        $this->emailVerifiedDate = new UserEmailVerifiedDate((new \DateTime())->format('Y-m-d H:i:s'));
        $this->verificationToken = new UserVerificationToken(null);
    }

    public function clearVerificationToken(): void
    {
        $this->verificationToken = new UserVerificationToken(null);
    }

    public static function create(
        UserName $name,
        UserEmail $email,
        UserEmailVerifiedDate $emailVerifiedDate,
        UserPassword $password,
        UserRememberToken $rememberToken,
        UserStatus $status,
        UserMainAdmin $isMainAdmin,
        UserVerificationToken $verificationToken,
        array $roles = [],
        ?UserId $id = null
    ): User
    {
        $user = new self($name, $email, $emailVerifiedDate, $password, $rememberToken, $status, $isMainAdmin, $verificationToken, $roles, $id);

        return $user;
    }
}
