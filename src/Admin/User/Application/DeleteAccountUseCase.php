<?php

declare(strict_types=1);

namespace Src\Admin\User\Application;

use Src\Admin\User\Domain\Contracts\UserRepositoryContract;
use Src\Admin\User\Domain\ValueObjects\UserId;

final class DeleteAccountUseCase
{
    private UserRepositoryContract $repository;

    public function __construct(UserRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $userId): void
    {
        $id = new UserId($userId);
        $user = $this->repository->findById($id);

        if (!$user) {
            throw new \Exception("User not found");
        }

        if ($user->isMainAdmin()->value()) {
            throw new \LogicException("The primary administrator account cannot be deleted.");
        }

        $this->repository->delete($id);
    }
}
