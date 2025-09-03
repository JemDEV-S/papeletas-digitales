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
            
            // Jefe de RRHH
            [
                'dni' => '40956781',
                'first_name' => 'Adrian Adel',
                'last_name' => 'Valer Bellota',
                'email' => 'a.valer@munisanjeronimocusco.gob.pe',
                'password' => Hash::make('40956781'),
                'department_id' => $recursosHumanos->id,
                'role_id' => $hrRole->id,
                'immediate_supervisor_id' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            
            // Gerente de Planeamiento y Presupuesto - Gian Franco (jefe inmediato de Marcelo y Tony)
            [
                'dni' => '73980928',
                'first_name' => 'Gian Franco',
                'last_name' => 'Quispe Huaman',
                'email' => 'g.quispe@munisanjeronimocusco.gob.pe',
                'password' => Hash::make('73980928'),
                'department_id' => $gerenciaPlaneamiento->id,
                'role_id' => $supervisorRole->id,
                'immediate_supervisor_id' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            
            // Jefe Inmediato - Marcelo (jefe inmediato de Manuel)
            [
                'dni' => '70576281',
                'first_name' => 'Marcelo Angelo',
                'last_name' => 'Vizcarra Vargas',
                'email' => 'm.vizcarra@munisanjeronimocusco.gob.pe',
                'password' => Hash::make('70576281'),
                'department_id' => $gerenciaPlaneamiento->id,
                'role_id' => $supervisorRole->id,
                'immediate_supervisor_id' => 3, // Gian Franco
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            
            // Jefe Inmediato - Tony (jefe inmediato de Alex)
            [
                'dni' => '41407887',
                'first_name' => 'Tony',
                'last_name' => 'Serrano Carbajal',
                'email' => 'tony.serrano@munisanjeronimocusco.gob.pe',
                'password' => Hash::make('41407887'),
                'department_id' => $oficinaTecnologias->id,
                'role_id' => $supervisorRole->id,
                'immediate_supervisor_id' => 3, // Gian Franco
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            
            // Gerente - Rodolfo (jefe inmediato de Jhon)
            [
                'dni' => '43236841',
                'first_name' => 'Rodolfo',
                'last_name' => 'Cordova Mamani',
                'email' => 'r.cordova@munisanjeronimocusco.gob.pe',
                'password' => Hash::make('43236841'),
                'department_id' => $gerenciaAdministracion->id,
                'role_id' => $supervisorRole->id,
                'immediate_supervisor_id' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            
            // Jefe Inmediato - Jhon
            [
                'dni' => '76576797',
                'first_name' => 'Jhon Edison',
                'last_name' => 'Collantes Hanampa',
                'email' => 'lovejhoi123456@gmail.com',
                'password' => Hash::make('76576797'),
                'department_id' => $gerenciaAdministracion->id,
                'role_id' => $supervisorRole->id,
                'immediate_supervisor_id' => 6, // Rodolfo
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            
            // Empleado - Manuel (bajo Marcelo)
            [
                'dni' => '76412311',
                'first_name' => 'Manuel Rodrigo',
                'last_name' => 'Cuchuyrumi Mamani',
                'email' => 'cuchuyrumimamani@gmail.com',
                'password' => Hash::make('76412311'),
                'department_id' => $gerenciaPlaneamiento->id,
                'role_id' => $employeeRole->id,
                'immediate_supervisor_id' => 4, // Marcelo
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            
            // Empleado - Alex (bajo Tony)
            [
                'dni' => '78016752',
                'first_name' => 'Alex Sandro',
                'last_name' => 'Leon Guzmán',
                'email' => 'oti1@munisanjeronimocusco.gob.pe',
                'password' => Hash::make('78016752'),
                'department_id' => $oficinaTecnologias->id,
                'role_id' => $employeeRole->id,
                'immediate_supervisor_id' => 5, // Tony
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            
            // Usuarios adicionales de ejemplo para otros departamentos
            [
                'dni' => '10000010',
                'first_name' => 'Carlos',
                'last_name' => 'Mamani Quispe',
                'email' => 'cmamani@munisanjeronimocusco.gob.pe',
                'password' => Hash::make('empleado123'),
                'department_id' => $gerenciaDesarrolloUrbano->id,
                'role_id' => $employeeRole->id,
                'immediate_supervisor_id' => 7, // Jhon
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            
            [
                'dni' => '10000011',
                'first_name' => 'María',
                'last_name' => 'Condori Flores',
                'email' => 'mcondori@munisanjeronimocusco.gob.pe',
                'password' => Hash::make('empleado123'),
                'department_id' => $gerenciaServicios->id,
                'role_id' => $employeeRole->id,
                'immediate_supervisor_id' => 6, // Rodolfo
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            
            // Usuario de prueba genérico
            [
                'dni' => '12345678',
                'first_name' => 'Demo',
                'last_name' => 'Usuario Prueba',
                'email' => 'demo@munisanjeronimocusco.gob.pe',
                'password' => Hash::make('demo123'),
                'department_id' => $gerenciaPlaneamiento->id,
                'role_id' => $employeeRole->id,
                'immediate_supervisor_id' => 4, // Marcelo
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