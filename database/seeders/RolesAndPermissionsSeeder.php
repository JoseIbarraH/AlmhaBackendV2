<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // 1. Limpiar la caché de Spatie (MUY IMPORTANTE)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Crear Permisos indicando explícitamente el guard 'api'
        Permission::firstOrCreate(['name' => 'crear usuarios', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'editar usuarios', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'eliminar usuarios', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'buscar usuario', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'listar usuarios', 'guard_name' => 'api']);

        // 3. Crear Roles indicando el guard 'api' y asignar permisos
        $roleAdmin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $roleAdmin->syncPermissions(['crear usuarios', 'editar usuarios', 'eliminar usuarios', 'buscar usuario', 'listar usuarios']);
        $roleEditor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'api']);
        $roleEditor->syncPermissions(['crear usuarios', 'editar usuarios', 'eliminar usuarios', 'buscar usuario', 'listar usuarios']);

        // 4. (Opcional) Asignarle el rol admin al usuario #1 si existe
        $user = \App\Models\User::find(1);
        if ($user) {
            echo("ROl asignado");
            // Nota: El modelo User debe estar asociado al guard api o Spatie lo detectará automáticamente.
            $user->assignRole('admin');
            $user->assignRole('editor');
        }
    }
}
