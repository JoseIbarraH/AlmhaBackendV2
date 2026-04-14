<?php

declare(strict_types=1);

namespace Src\Admin\User\Application;

use Illuminate\Support\Facades\Hash;
use Src\Admin\User\Domain\Contracts\UserRepositoryContract;
use Src\Admin\User\Domain\ValueObjects\UserId;
use Src\Admin\User\Domain\ValueObjects\UserName;
use Src\Admin\User\Domain\ValueObjects\UserPassword;

final class UpdateProfileUseCase
{
    private UserRepositoryContract $repository;

    public function __construct(UserRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $userId, string $name, ?string $password = null): void
    {
        $user = $this->repository->findById(new UserId($userId));

        if (!$user) {
            throw new \Exception("User not found");
        }

        $newName = new UserName($name);
        $newPassword = $password ? new UserPassword(Hash::make($password)) : $user->password();

        $updatedUser = clone $user;
        
        // We use a private reflection or a dedicated method if we had one, 
        // but since we are in a simple domain, let's just create a new one with same ID
        $user = \Src\Admin\User\Domain\Entity\User::create(
            $newName,
            $user->email(),
            $user->emailVerifiedDate(),
            $newPassword,
            $user->rememberToken(),
            $user->status(),
            $user->isMainAdmin(),
            $user->roles(),
            $user->id()
        );

        $this->repository->update($user);
    }
}
