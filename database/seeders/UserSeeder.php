<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('name', Role::ADMIN)->first();
        $hrRole = Role::where('name', Role::JEFE_RRHH)->first();
        $supervisorRole = Role::where('name', Role::JEFE_INMEDIATO)->first();
        $employeeRole = Role::where('name', Role::EMPLEADO)->first();

        // Get departments
        $alcaldia = Department::where('code', 'ALC')->first();
        $gerenciaMunicipal = Department::where('code', 'GM')->first();
        $recursosHumanos = Department::where('code', 'URH')->first();
        $gerenciaPlaneamiento = Department::where('code', 'GPP')->first();
        $oficinaTecnologias = Department::where('code', 'OTI')->first();
        $gerenciaAdministracion = Department::where('code', 'GA')->first();
        $gerenciaDesarrolloUrbano = Department::where('code', 'GDU')->first();
        $gerenciaServicios = Department::where('code', 'GS')->first();

        $users = [
            // Administrador del Sistema
            [
                'dni' => '10000001',
                'first_name' => 'Admin',
                'last_name' => 'Sistema',
                'email' => 'admin@munisanjeronimocusco.gob.pe',
                'password' => Hash::make('admin123'),
                'department_id' => $oficinaTecnologias->id,
                'role_id' => $adminRole->id,
                'immediate_supervisor_id' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        $this->command->info('Usuarios creados:');
        $this->command->info('----------------------------------------');
        $this->command->info('Admin: admin@munisanjeronimocusco.gob.pe / admin123');
        $this->command->info('Jefe RRHH: a.valer@munisanjeronimocusco.gob.pe / 40956781');
        $this->command->info('Gerente GPP: g.quispe@munisanjeronimocusco.gob.pe / 73980928');
        $this->command->info('Jefe Planeamiento: m.vizcarra@munisanjeronimocusco.gob.pe / 70576281');
        $this->command->info('Jefe OTI: tony.serrano@munisanjeronimocusco.gob.pe / 41407887');
        $this->command->info('Gerente Admin: r.cordova@munisanjeronimocusco.gob.pe / 43236841');
        $this->command->info('Demo: demo@munisanjeronimocusco.gob.pe / demo123');
        $this->command->info('----------------------------------------');
    }
}