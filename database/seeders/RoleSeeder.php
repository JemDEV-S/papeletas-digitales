<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => Role::EMPLEADO,
                'description' => 'Empleado regular de la institución',
                'permissions' => [
                    'permission.create',
                    'permission.view.own',
                    'permission.edit.own',
                    'permission.cancel.own',
                ],
            ],
            [
                'name' => Role::JEFE_INMEDIATO,
                'description' => 'Jefe inmediato con capacidad de aprobación nivel 1',
                'permissions' => [
                    'permission.create',
                    'permission.view.own',
                    'permission.view.subordinates',
                    'permission.edit.own',
                    'permission.cancel.own',
                    'permission.approve.level1',
                    'report.view.department',
                ],
            ],
            [
                'name' => Role::JEFE_RRHH,
                'description' => 'Jefe de Recursos Humanos con aprobación nivel 2',
                'permissions' => [
                    'permission.create',
                    'permission.view.all',
                    'permission.edit.own',
                    'permission.cancel.own',
                    'permission.approve.level2',
                    'report.view.all',
                    'report.export',
                    'user.view.all',
                ],
            ],
            [
                'name' => Role::ADMIN,
                'description' => 'Administrador del sistema con acceso total',
                'permissions' => [
                    'permission.create',
                    'permission.view.all',
                    'permission.edit.all',
                    'permission.delete.all',
                    'permission.approve.all',
                    'user.create',
                    'user.view.all',
                    'user.edit.all',
                    'user.delete',
                    'department.manage',
                    'role.manage',
                    'permissiontype.manage',
                    'system.configure',
                    'report.view.all',
                    'report.export',
                    'audit.view',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            Role::create($roleData);
        }
    }
}