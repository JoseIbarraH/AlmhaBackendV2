<?php

declare(strict_types=1);

namespace Src\Admin\User\Application;

use Src\Admin\User\Domain\Contracts\UserRepositoryContract;
use Src\Admin\User\Domain\ValueObjects\UserId;
use Src\Admin\User\Domain\Exceptions\UserNotFoundException;

final class DeleteUserUseCase
{
    private UserRepositoryContract $repository;

    public function __construct(UserRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $id): void
    {
        $userId = new UserId((int)$id);
        $user = $this->repository->findById($userId);

        if (!$user) {
            throw new UserNotFoundException($id);
        }

        $this->repository->delete($userId);
    }
}
