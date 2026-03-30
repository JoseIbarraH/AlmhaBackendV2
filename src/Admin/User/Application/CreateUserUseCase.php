<?php

declare(strict_types=1);

namespace Src\Admin\User\Application;

use DateTime;
use Src\Admin\User\Domain\Contracts\UserRepositoryContract;
use Src\Admin\User\Domain\Entity\User;
use Src\Admin\User\Domain\ValueObjects\UserEmail;
use Src\Admin\User\Domain\ValueObjects\UserEmailVerifiedDate;
use Src\Admin\User\Domain\ValueObjects\UserName;
use Src\Admin\User\Domain\ValueObjects\UserPassword;
use Src\Admin\User\Domain\ValueObjects\UserRememberToken;

final class CreateUserUseCase
{
    private $repository;

    public function __construct(UserRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(
        string $userName,
        string $userEmail,
        ?DateTime $userEmailVerifiedDate,
        string $userPassword,
        ?string $userRememberToken,
        ?bool $isActive = true,
        array $roles = []
    ): void
    {
        $name = new UserName($userName);
        $email = new UserEmail($userEmail);
        $emailVerifiedDate = new UserEmailVerifiedDate($userEmailVerifiedDate);
        $password = new UserPassword($userPassword);
        $rememberToken = new UserRememberToken($userRememberToken);
        $status = new \Src\Admin\User\Domain\ValueObjects\UserStatus($isActive);

        $user = User::create($name, $email, $emailVerifiedDate, $password, $rememberToken, $status, $roles);

        $this->repository->save($user);

    }
}
