<?php

namespace Src\Admin\Role\Infrastructure\Repositories;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Src\Admin\Role\Domain\Contracts\RoleRepositoryContract;
use Src\Admin\Role\Domain\Exceptions\RoleAlreadyExistsException;
use Src\Admin\Role\Domain\Exceptions\RoleNotFoundException;

class SpatieRoleRepository implements RoleRepositoryContract
{
    /**
     * @param string $roleName
     * @return void
     * @throws RoleAlreadyExistsException
     */
    public function createRole(string $roleName): void
    {
        if (Role::where('name', $roleName)->exists()) {
            throw new RoleAlreadyExistsException($roleName);
        }

        Role::create(['name' => $roleName]);
    }

    /**
     * @param string|int $userId
     * @param string $roleName
     * @return void
     * @throws RoleNotFoundException
     */
    public function assignRoleToUser($userId, string $roleName): void
    {
        if (!Role::where('name', $roleName)->exists()) {
            throw new RoleNotFoundException($roleName);
        }

        $user = User::findOrFail($userId);
        $user->assignRole($roleName);
    }

    /**
     * @return array
     */
    public function getAllRoles(): array
    {
        return Role::all()->toArray();
    }

    /**
     * @return array
     */
    public function getAllPermissions(): array
    {
        return \Spatie\Permission\Models\Permission::all()->toArray();
    }
}
