<?php

namespace Src\Admin\Role\Application;

use Src\Admin\Role\Domain\Contracts\RoleRepositoryContract;

class AssignRoleToUserUseCase
{
    private RoleRepositoryContract $repository;

    public function __construct(RoleRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string|int $userId
     * @param string $roleName
     * @return void
     * @throws \Src\Admin\Role\Domain\Exceptions\RoleNotFoundException
     */
    public function execute($userId, string $roleName): void
    {
        $this->repository->assignRoleToUser($userId, $roleName);
    }
}
