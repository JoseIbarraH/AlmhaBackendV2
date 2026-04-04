<?php

declare(strict_types=1);

namespace Src\Admin\User\Application;

use Src\Admin\User\Domain\Contracts\UserRepositoryContract;

final class GetAllUsersUseCase
{
    private UserRepositoryContract $repository;

    public function __construct(UserRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $page = 1, int $perPage = 15): array
    {
        return $this->repository->getAll($page, $perPage);
    }
}
