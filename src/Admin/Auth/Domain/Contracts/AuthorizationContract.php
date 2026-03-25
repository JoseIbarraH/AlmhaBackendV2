<?php

namespace Src\Admin\Auth\Domain\Contracts;

interface AuthorizationContract
{
    /**
     * Verifica si un usuario tiene un permiso específico.
     *
     * @param string|int $userId
     * @param string $permission
     * @return bool
     */
    public function hasPermission($userId, string $permission): bool;

    /**
     * Asigna un rol a un usuario por su ID.
     *
     * @param string|int $userId
     * @param string $role
     * @return void
     */
    public function assignRole($userId, string $role): void;
}
