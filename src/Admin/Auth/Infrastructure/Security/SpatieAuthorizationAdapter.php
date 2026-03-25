<?php

namespace Src\Admin\Auth\Infrastructure\Security;

use App\Models\User;
use Src\Admin\Auth\Domain\Contracts\AuthorizationContract;

class SpatieAuthorizationAdapter implements AuthorizationContract
{
    /**
     * @param string|int $userId
     * @param string $permission
     * @return bool
     */
    public function hasPermission($userId, string $permission): bool
    {
        $userModel = User::find($userId);
        
        if (!$userModel) {
            return false;
        }

        return $userModel->hasPermissionTo($permission);
    }

    /**
     * @param string|int $userId
     * @param string $role
     * @return void
     */
    public function assignRole($userId, string $role): void
    {
        $userModel = User::findOrFail($userId);
        $userModel->assignRole($role);
    }
}
