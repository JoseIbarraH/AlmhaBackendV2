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
        // 1. Limpiar la caché de Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Definir Permisos por Módulos
        $permissionsByModule = [
            'users' => ['view_users', 'create_users', 'edit_users', 'delete_users', 'search_users', 'view_user_detail'],
            'blogs' => [
                'view_blogs', 'create_blogs', 'edit_blogs', 'delete_blogs', 'change_blog_status', 'view_blog_detail',
                'view_blog_categories', 'create_blog_categories', 'edit_blog_categories', 'delete_blog_categories'
            ],
            'procedures' => [
                'view_procedures', 'create_procedures', 'edit_procedures', 'delete_procedures', 'view_procedure_detail',
                'view_procedure_categories', 'create_procedure_categories', 'edit_procedure_categories', 'delete_procedure_categories'
            ],
            'teams' => ['view_teams', 'create_teams', 'edit_teams', 'delete_teams', 'view_team_detail'],
            'audits' => ['view_audits'],
            'roles' => ['view_roles', 'assign_roles', 'view_permissions'],
            'designs' => ['view_designs', 'create_designs', 'edit_designs', 'delete_designs'],
        ];

        $allPermissionNames = [];
        foreach ($permissionsByModule as $module => $permissions) {
            foreach ($permissions as $permissionName) {
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'api']);
                $allPermissionNames[] = $permissionName;
            }
        }

        // 3. Definir Roles y sus Traducciones
        $rolesData = [
            'super_admin' => [
                'titles' => ['es' => 'Super Administrador', 'en' => 'Super Administrator'],
                'permissions' => $allPermissionNames
            ],
            'user_manager' => [
                'titles' => ['es' => 'Gestor de Usuarios', 'en' => 'User Manager'],
                'permissions' => array_merge($permissionsByModule['users'], $permissionsByModule['roles'])
            ],
            'blog_manager' => [
                'titles' => ['es' => 'Gestor de Blog', 'en' => 'Blog Manager'],
                'permissions' => $permissionsByModule['blogs']
            ],
            'procedure_manager' => [
                'titles' => ['es' => 'Gestor de Procedimientos', 'en' => 'Procedure Manager'],
                'permissions' => $permissionsByModule['procedures']
            ],
            'team_manager' => [
                'titles' => ['es' => 'Gestor de Equipo', 'en' => 'Team Manager'],
                'permissions' => $permissionsByModule['teams']
            ],
            'audit_viewer' => [
                'titles' => ['es' => 'Visor de Auditoría', 'en' => 'Audit Viewer'],
                'permissions' => $permissionsByModule['audits']
            ],
            'setting_manager' => [
                'titles' => ['es' => 'Gestor de Diseño', 'en' => 'Design Manager'],
                'permissions' => $permissionsByModule['designs']
            ],
        ];

        foreach ($rolesData as $roleName => $data) {
            $role = \Src\Admin\Role\Infrastructure\Models\CustomRole::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'api'
            ]);

            // Guardar traducciones
            foreach ($data['titles'] as $lang => $title) {
                \Src\Admin\Role\Infrastructure\Models\RoleTranslationModel::updateOrCreate(
                    ['role_id' => $role->id, 'lang' => $lang],
                    ['title' => $title]
                );
            }

            $role->syncPermissions($data['permissions']);
        }
    }
}
