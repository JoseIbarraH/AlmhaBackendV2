<?php

namespace Src\Admin\Role\Application;

use Src\Admin\Role\Domain\Contracts\RoleRepositoryContract;

class CreateRoleUseCase
{
    private RoleRepositoryContract $repository;

    public function __construct(RoleRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $roleName
     * @return void
     * @throws \Src\Admin\Role\Domain\Exceptions\RoleAlreadyExistsException
     */
    public function execute(string $roleName): void
    {
        $this->repository->createRole($roleName);
    }
}
