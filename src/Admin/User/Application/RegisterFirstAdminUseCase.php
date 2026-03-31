<?php

declare(strict_types=1);

namespace Src\Admin\User\Application;

use Src\Admin\User\Domain\Contracts\UserRepositoryContract;
use Src\Admin\User\Domain\Entity\User;
use Src\Admin\User\Domain\ValueObjects\UserEmail;
use Src\Admin\User\Domain\ValueObjects\UserEmailVerifiedDate;
use Src\Admin\User\Domain\ValueObjects\UserName;
use Src\Admin\User\Domain\ValueObjects\UserPassword;
use Src\Admin\User\Domain\ValueObjects\UserRememberToken;
use Src\Admin\User\Domain\ValueObjects\UserStatus;

final class RegisterFirstAdminUseCase
{
    private UserRepositoryContract $repository;

    public function __construct(UserRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(
        string $userName,
        string $userEmail,
        string $userPassword
    ): void
    {
        if ($this->repository->hasAdmin()) {
            throw new \LogicException('The system is already initialized with an administrator.');
        }

        $name = new UserName($userName);
        $email = new UserEmail($userEmail);
        $emailVerifiedDate = new UserEmailVerifiedDate(now()->toDateTimeString());
        $password = new UserPassword($userPassword);
        $rememberToken = new UserRememberToken(null);
        $status = new UserStatus(true);
        $roles = ['super_admin'];

        $user = User::create($name, $email, $emailVerifiedDate, $password, $rememberToken, $status, $roles);

        $this->repository->save($user);
    }
}
