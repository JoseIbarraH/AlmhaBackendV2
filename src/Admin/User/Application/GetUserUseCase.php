<?php

declare(strict_types=1);

namespace Src\Admin\User\Application;

use Src\Admin\User\Domain\Contracts\UserRepositoryContract;
use Src\Admin\User\Domain\Entity\User;
use Src\Admin\User\Domain\ValueObjects\UserId;
use Src\Admin\User\Domain\Exceptions\UserNotFoundException;

final class GetUserUseCase
{
    private UserRepositoryContract $repository;

    public function __construct(UserRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $id
     * @return User
     * @throws UserNotFoundException
     */
    public function execute(string $id): User
    {
        $user = $this->repository->findById(new UserId((int)$id));

        if (!$user) {
            throw new UserNotFoundException($id);
        }

        return $user;
    }
}
