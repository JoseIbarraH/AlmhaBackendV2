<?php

namespace Src\Admin\Role\Application;

use Src\Admin\Role\Domain\Contracts\RoleRepositoryContract;

class GetAllPermissionsUseCase
{
    private RoleRepositoryContract $repository;

    public function __construct(RoleRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        return $this->repository->getAllPermissions();
    }
}
