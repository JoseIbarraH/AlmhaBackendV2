<?php

namespace Src\Admin\Role\Domain\Contracts;

interface RoleRepositoryContract
{
    /**
     * @param string $name
     * @return void
     * @throws \Src\Admin\Role\Domain\Exceptions\RoleAlreadyExistsException
     */
    public function createRole(string $name): void;

    /**
     * @param string|int $userId
     * @param string $roleName
     * @return void
     * @throws \Src\Admin\Role\Domain\Exceptions\RoleNotFoundException
     */
    public function assignRoleToUser($userId, string $roleName): void;

    /**
     * @return array
     */
    public function getAllRoles(int $page = 1, int $perPage = 15): array;

    /**
     * @return array
     */
    public function getAllPermissions(int $page = 1, int $perPage = 15): array;
}
