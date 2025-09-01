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
        $contabilidad = Department::where('code', 'UC')->first();
        $tecnologia = Department::where('code', 'UTI')->first();
        $obrasPublicas = Department::where('code', 'UOP')->first();

        $users = [
            // Administrador del Sistema
            [
                'dni' => '10000001',
                'first_name' => 'Admin',
                'last_name' => 'Sistema',
                'email' => 'admin@municipalidad.gob.pe',
                'password' => Hash::make('admin123'),
                'department_id' => $tecnologia->id,
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
                'email' => 'adrianb@municipalidad.gob.pe',
                'password' => Hash::make('40956781'),
                'department_id' => $recursosHumanos->id,
                'role_id' => $hrRole->id,
                'immediate_supervisor_id' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            
            // Jefe Inmediato - Contabilidad
            [
                'dni' => '43236841',
                'first_name' => 'Rodolfo',
                'last_name' => 'Cordoba Mamani',
                'email' => 'crodriguez@municipalidad.gob.pe',
                'password' => Hash::make('43236841'),
                'department_id' => $contabilidad->id,
                'role_id' => $supervisorRole->id,
                'immediate_supervisor_id' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            
            // Jefe Inmediato - Obras Públicas
            [
                'dni' => '10000004',
                'first_name' => 'Luis',
                'last_name' => 'Mendoza Silva',
                'email' => 'lmendoza@municipalidad.gob.pe',
                'password' => Hash::make('jefe123'),
                'department_id' => $obrasPublicas->id,
                'role_id' => $supervisorRole->id,
                'immediate_supervisor_id' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            
            // Empleados de Contabilidad
            [
                'dni' => '73980928',
                'first_name' => 'Gian Franco',
                'last_name' => 'Quispe Huaman',
                'email' => 'atorres@municipalidad.gob.pe',
                'password' => Hash::make('73980928'),
                'department_id' => $contabilidad->id,
                'role_id' => $employeeRole->id,
                'immediate_supervisor_id' => 3, // Carlos Rodríguez
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'dni' => '10000006',
                'first_name' => 'José',
                'last_name' => 'Quispe Mamani',
                'email' => 'jquispe@municipalidad.gob.pe',
                'password' => Hash::make('empleado123'),
                'department_id' => $contabilidad->id,
                'role_id' => $employeeRole->id,
                'immediate_supervisor_id' => 3, // Carlos Rodríguez
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            
            // Empleados de Obras Públicas
            [
                'dni' => '10000007',
                'first_name' => 'Roberto',
                'last_name' => 'Flores Chávez',
                'email' => 'rflores@municipalidad.gob.pe',
                'password' => Hash::make('empleado123'),
                'department_id' => $obrasPublicas->id,
                'role_id' => $employeeRole->id,
                'immediate_supervisor_id' => 4, // Luis Mendoza
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'dni' => '10000008',
                'first_name' => 'Patricia',
                'last_name' => 'Vargas Luna',
                'email' => 'pvargas@municipalidad.gob.pe',
                'password' => Hash::make('empleado123'),
                'department_id' => $obrasPublicas->id,
                'role_id' => $employeeRole->id,
                'immediate_supervisor_id' => 4, // Luis Mendoza
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            
            // Usuario de prueba genérico
            [
                'dni' => '12345678',
                'first_name' => 'Juan',
                'last_name' => 'Pérez Demo',
                'email' => 'demo@municipalidad.gob.pe',
                'password' => Hash::make('demo123'),
                'department_id' => $contabilidad->id,
                'role_id' => $employeeRole->id,
                'immediate_supervisor_id' => 3, // Carlos Rodríguez
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        $this->command->info('Usuarios creados:');
        $this->command->info('----------------------------------------');
        $this->command->info('Admin: admin@municipalidad.gob.pe / admin123');
        $this->command->info('Jefe RRHH: mgarcia@municipalidad.gob.pe / rrhh123');
        $this->command->info('Jefe Inmediato: crodriguez@municipalidad.gob.pe / jefe123');
        $this->command->info('Empleado: demo@municipalidad.gob.pe / demo123');
        $this->command->info('----------------------------------------');
    }
}