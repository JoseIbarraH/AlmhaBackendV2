<?php

declare(strict_types=1);

namespace Src\Admin\User\Application;

use Src\Admin\User\Domain\Contracts\UserRepositoryContract;
use Src\Admin\User\Domain\ValueObjects\UserId;
use Src\Admin\User\Domain\ValueObjects\UserName;
use Src\Admin\User\Domain\ValueObjects\UserEmail;
use Src\Admin\User\Domain\ValueObjects\UserPassword;
use Src\Admin\User\Domain\Exceptions\UserNotFoundException;
use Illuminate\Support\Facades\Hash;
use Src\Admin\User\Domain\Entity\User;
use Src\Admin\User\Domain\ValueObjects\UserMainAdmin;

final class UpdateUserUseCase
{
    private UserRepositoryContract $repository;

    public function __construct(UserRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $id, string $name, string $email, ?string $password, ?bool $isActive = null, array $roles = []): void
    {
        $user = $this->repository->findById(new UserId($id));

        if (!$user) {
            throw new UserNotFoundException($id);
        }

        $passwordVo = $password 
            ? new UserPassword(Hash::make($password)) 
            : $user->password();
            
        $statusVo = ($isActive !== null) 
            ? new \Src\Admin\User\Domain\ValueObjects\UserStatus($isActive)
            : $user->status();

        $updatedUser = new User(
            new UserName($name),
            new UserEmail($email),
            $user->emailVerifiedDate(),
            $passwordVo,
            $user->rememberToken(),
            $statusVo,
            $user->isMainAdmin(),
            $user->verificationToken(),
            $roles,
            $user->id()
        );

        $this->repository->update($updatedUser);
    }
}
