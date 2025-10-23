<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Department;
use App\Models\User;
use App\Models\PermissionType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class ImportExcelDataCommand extends Command
{
    protected $signature = 'import:excel-data {file}';
    protected $description = 'Importa datos del Excel identificando jefes por cargo exacto';

    // Cachés
    private $roleCache = [];
    private $departmentCache = [];
    private $userCache = [];
    
    // Cargos exactos que son JEFES INMEDIATOS
    private $jefesInmediatoCargos = [
        'GERENTE',
        'SUBGERENTE',
        'GERENTE MUNICIPAL',
        'JEFE',
        'RESPONSABLE DE UNIDAD',
        'RESPONSABLE DE LA OFICINA',
        'RESPONSABLE DE LUDOTECA',
        'SECRETARIA GENERAL',
        'ENCARGADO',
        'ENCARGADA',
        'RESPONSABLE DE CONTROL DE DEUDA',
        'RESPONSABLE DEL PROGRAMA DE VALORIZACION DE RESIDUOS SOLIDOS',
        'RESPONSABLE ZOONOSIS Y EDUCACION AMBIENTAL',
        'RESPONSABLE DE BIBLIOTECA',
        'RESPONSABLE DE ATM',
        'DIRECTOR',
        'DIRECTOR ENCARGADO',
        'DIRECTOR GENERAL',
        'ADMINISTRADOR',
        'EJECUTOR COACTIVO',
        'COORDINADORA',
        'COORDINADOR',
        'SUPERVISOR',
        'RESIDENTE',
        'PROCURADOR PúBLICO MUNICIPAL',
        'FISCALIZADOR',
        'INSPECTOR DE OBRA',
        'INSPECTOR',
        'LIQUIDADOR TECNICO',
        'LIQUIDADOR FINANCIERO',
        'ASESOR TECNICO',
    ];
    
    // Cargos que pueden ser RRHH (si están en departamento de RRHH o Personal)
    private $rrhhCargos = [
        'asistente de remuneraciones',
    ];
    
    // Máxima autoridad
    private $adminCargos = [
        'ALCALDE',
    ];

    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("El archivo no existe: {$filePath}");
            return 1;
        }

        DB::beginTransaction();
        
        try {
            $this->info('🚀 Iniciando importación con identificación por cargo...');
            $this->newLine();
            
            $excelData = Excel::toArray([], $filePath);
            
            $this->step1_CreateRoles();
            $this->step2_CreatePermissionTypes();
            $this->step3_ImportSedes($excelData);
            $this->step4_ImportDependencias($excelData);
            $this->step5_ImportPersonalWithRoles($excelData);
            $this->step6_AssignSupervisorsByHierarchy();
            $this->step7_AssignDepartmentManagers();
            
            DB::commit();
            
            $this->newLine();
            $this->info('✅ Importación completada exitosamente');
            $this->showStatistics();
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    private function step1_CreateRoles()
    {
        $this->info('📝 PASO 1/7: Creando Roles...');
        
        $roles = [
            ['name' => 'empleado', 'description' => 'Empleado regular', 'permissions' => json_encode(['view_own_requests', 'create_request'])],
            ['name' => 'jefe_inmediato', 'description' => 'Jefe Inmediato / Supervisor', 'permissions' => json_encode(['view_own_requests', 'create_request', 'approve_level1'])],
            ['name' => 'jefe_rrhh', 'description' => 'Jefe de Recursos Humanos', 'permissions' => json_encode(['view_all_requests', 'approve_level2', 'manage_permissions'])],
            ['name' => 'admin', 'description' => 'Administrador del Sistema', 'permissions' => json_encode(['*'])]
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(['name' => $roleData['name']], $roleData);
            $this->roleCache[$roleData['name']] = $role;
        }
        
        $this->line('   ✓ Roles creados');
    }

    private function step2_CreatePermissionTypes()
    {
        $this->info('📝 PASO 2/7: Creando Tipos de Permiso...');
        
        $types = [
            ['name' => 'Permiso por Enfermedad', 'code' => 'enfermedad', 'description' => 'Permiso por motivos de salud', 'max_hours_per_day' => 8, 'max_hours_per_month' => 40, 'max_times_per_month' => 5, 'requires_document' => true, 'with_pay' => true, 'is_active' => true],
            ['name' => 'Permiso por Gravidez', 'code' => 'gravidez', 'description' => 'Permiso por control prenatal', 'max_hours_per_day' => 4, 'max_hours_per_month' => 16, 'max_times_per_month' => 4, 'requires_document' => true, 'with_pay' => true, 'is_active' => true],
            ['name' => 'Permiso Personal', 'code' => 'personal', 'description' => 'Permiso por motivos personales', 'max_hours_per_day' => 4, 'max_hours_per_month' => 8, 'max_times_per_month' => 2, 'requires_document' => false, 'with_pay' => false, 'is_active' => true],
        ];

        foreach ($types as $typeData) {
            PermissionType::firstOrCreate(['code' => $typeData['code']], $typeData);
        }
        
        $this->line('   ✓ Tipos de permiso creados');
    }

    private function step3_ImportSedes($excelData)
    {
        $this->info('🏢 PASO 3/7: Importando Sedes...');
        
        $dependenciasSheet = $excelData[1] ?? [];
        $sedesUnique = [];

        foreach ($dependenciasSheet as $index => $row) {
            if ($index === 0) continue;
            
            $idSede = $row[0] ?? null;
            $nombreSede = $row[1] ?? null;
            
            if ($idSede && $nombreSede && !isset($sedesUnique[$idSede])) {
                $sedesUnique[$idSede] = $nombreSede;
            }
        }

        $count = 0;
        foreach ($sedesUnique as $idSede => $nombreSede) {
            $code = 'SEDE-' . str_pad($idSede, 3, '0', STR_PAD_LEFT);
            
            $department = Department::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $nombreSede,
                    'description' => 'Sede principal',
                    'parent_department_id' => null,
                    'is_active' => true
                ]
            );
            
            $this->departmentCache["sede_{$idSede}"] = $department;
            $count++;
        }
        
        $this->line("   ✓ {$count} sedes importadas");
    }

    private function step4_ImportDependencias($excelData)
    {
        $this->info('🏗️ PASO 4/7: Importando Dependencias...');
        
        $dependenciasSheet = $excelData[1] ?? [];
        $dependencias = [];
        
        foreach ($dependenciasSheet as $index => $row) {
            if ($index === 0) continue;
            
            $dependencias[] = [
                'id_sede' => $row[0] ?? null,
                'id_dependencia' => $row[2] ?? null,
                'nombre_dependencia' => $row[3] ?? null,
                'id_dependencia_padre' => $row[4] ?? null,
                'descripcion' => $row[5] ?? '',
            ];
        }

        usort($dependencias, fn($a, $b) => 
            !$a['id_dependencia_padre'] ? -1 : (!$b['id_dependencia_padre'] ? 1 : 0)
        );

        $count = 0;
        foreach ($dependencias as $dep) {
            if (!$dep['id_dependencia'] || !$dep['nombre_dependencia']) continue;
            
            $code = 'DEP-' . str_pad($dep['id_dependencia'], 4, '0', STR_PAD_LEFT);
            
            $parentId = null;
            if ($dep['id_dependencia_padre']) {
                $parentCode = 'DEP-' . str_pad($dep['id_dependencia_padre'], 4, '0', STR_PAD_LEFT);
                $parent = Department::where('code', $parentCode)->first();
                $parentId = $parent?->id;
            } else {
                $parent = $this->departmentCache["sede_{$dep['id_sede']}"] ?? null;
                $parentId = $parent?->id;
            }
            
            $department = Department::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $dep['nombre_dependencia'],
                    'description' => $dep['descripcion'],
                    'parent_department_id' => $parentId,
                    'is_active' => true
                ]
            );
            
            $this->departmentCache["dep_{$dep['id_dependencia']}"] = $department;
            $count++;
        }
        
        $this->line("   ✓ {$count} dependencias importadas");
    }

    private function step5_ImportPersonalWithRoles($excelData)
    {
        $this->info('👥 PASO 5/7: Importando Personal y asignando roles...');
        
        $personalSheet = $excelData[0] ?? [];
        $stats = ['empleado' => 0, 'jefe_inmediato' => 0, 'jefe_rrhh' => 0, 'admin' => 0, 'errores' => 0];
        
        foreach ($personalSheet as $index => $row) {
            if ($index === 0) continue;
            
            $nroDoc = $row[6] ?? null;
            $apellido1 = $row[7] ?? '';
            $apellido2 = $row[8] ?? '';
            $nombres = $row[9] ?? '';
            $idDependencia = $row[2] ?? null;
            $nombreCargo = trim($row[11] ?? ''); // Columna "Nombre Cargo"
            
            if (!$nroDoc || !$nombres) {
                $stats['errores']++;
                continue;
            }
            
            // Determinar el rol según el cargo
            $roleName = $this->determineRoleFromCargo($nombreCargo);
            $role = $this->roleCache[$roleName];
            
            $depCode = 'DEP-' . str_pad($idDependencia, 4, '0', STR_PAD_LEFT);
            $department = Department::where('code', $depCode)->first();
            
            $email = $this->generateEmail($nombres, $apellido1);
            
            try {
                $user = User::firstOrCreate(
                    ['dni' => $nroDoc],
                    [
                        'first_name' => trim($nombres),
                        'last_name' => trim($apellido1 . ' ' . $apellido2),
                        'name' => trim($nombres . ' ' . $apellido1 . ' ' . $apellido2),
                        'email' => $email,
                        'password' => Hash::make($nroDoc),
                        'department_id' => $department?->id,
                        'role_id' => $role->id,
                        'is_active' => true
                    ]
                );
                
                $this->userCache[$nroDoc] = $user;
                $stats[$roleName]++;
                
            } catch (\Exception $e) {
                $this->warn("   ⚠ Error al importar {$nombres}: " . $e->getMessage());
                $stats['errores']++;
            }
        }
        
        $total = $stats['empleado'] + $stats['jefe_inmediato'] + $stats['jefe_rrhh'] + $stats['admin'];
        $this->line("   ✓ {$total} usuarios importados");
        $this->line("      • {$stats['empleado']} empleados");
        $this->line("      • {$stats['jefe_inmediato']} jefes inmediatos");
        $this->line("      • {$stats['jefe_rrhh']} jefes de RRHH");
        $this->line("      • {$stats['admin']} administradores");
        if ($stats['errores'] > 0) {
            $this->warn("      ⚠ {$stats['errores']} errores");
        }
    }

    private function determineRoleFromCargo(string $cargo): string
    {
        $cargoUpper = strtoupper(trim($cargo));
        
        // Verificar si es ADMIN (Alcalde)
        if (in_array($cargoUpper, $this->adminCargos)) {
            return 'admin';
        }
        
        // Verificar si es JEFE INMEDIATO
        if (in_array($cargoUpper, $this->jefesInmediatoCargos)) {
            return 'jefe_inmediato';
        }
        
        // Verificar si es RRHH
        if (in_array($cargo, $this->rrhhCargos)) {
            return 'jefe_rrhh';
        }
        
        // Por defecto: empleado
        return 'empleado';
    }

    private function step6_AssignSupervisorsByHierarchy()
    {
        $this->info('👔 PASO 6/7: Asignando Supervisores por jerarquía...');
        
        $departments = Department::whereNotNull('parent_department_id')->get();
        $assignedCount = 0;
        $warningsCount = 0;
        
        foreach ($departments as $dept) {
            $employees = User::where('department_id', $dept->id)->get();
            
            // Buscar un jefe en el MISMO departamento primero
            $localJefe = User::where('department_id', $dept->id)
                ->whereHas('role', fn($q) => $q->whereIn('name', ['jefe_inmediato', 'jefe_rrhh', 'admin']))
                ->orderByRaw("CASE 
                    WHEN role_id = (SELECT id FROM roles WHERE name = 'admin') THEN 1
                    WHEN role_id = (SELECT id FROM roles WHERE name = 'jefe_rrhh') THEN 2
                    WHEN role_id = (SELECT id FROM roles WHERE name = 'jefe_inmediato') THEN 3
                    ELSE 4 END")
                ->first();
            
            // Si no hay jefe local, buscar en el departamento padre
            $supervisor = $localJefe;
            
            if (!$supervisor && $dept->parent_department_id) {
                $supervisor = User::where('department_id', $dept->parent_department_id)
                    ->whereHas('role', fn($q) => $q->whereIn('name', ['jefe_inmediato', 'jefe_rrhh', 'admin']))
                    ->orderByRaw("CASE 
                        WHEN role_id = (SELECT id FROM roles WHERE name = 'admin') THEN 1
                        WHEN role_id = (SELECT id FROM roles WHERE name = 'jefe_rrhh') THEN 2
                        WHEN role_id = (SELECT id FROM roles WHERE name = 'jefe_inmediato') THEN 3
                        ELSE 4 END")
                    ->first();
            }
            
            if ($supervisor) {
                foreach ($employees as $emp) {
                    if ($emp->id !== $supervisor->id) {
                        $emp->update(['immediate_supervisor_id' => $supervisor->id]);
                        $assignedCount++;
                    }
                }
            } else {
                $this->warn("   ⚠ Departamento sin supervisor: {$dept->name}");
                $warningsCount++;
            }
        }
        
        // Asignar supervisores a los jefes de departamentos (que reporten al padre)
        $this->assignSupervisorsToJefes();
        
        $this->line("   ✓ {$assignedCount} supervisores asignados");
        if ($warningsCount > 0) {
            $this->warn("   ⚠ {$warningsCount} departamentos sin supervisor");
        }
    }

    private function assignSupervisorsToJefes()
    {
        // Los jefes de cada departamento reportan al jefe del departamento padre
        $jefes = User::whereHas('role', fn($q) => $q->where('name', 'jefe_inmediato'))
            ->whereNull('immediate_supervisor_id')
            ->get();
        
        foreach ($jefes as $jefe) {
            if (!$jefe->department_id) continue;
            
            $dept = Department::find($jefe->department_id);
            if (!$dept || !$dept->parent_department_id) continue;
            
            $supervisorJefe = User::where('department_id', $dept->parent_department_id)
                ->whereHas('role', fn($q) => $q->whereIn('name', ['jefe_inmediato', 'jefe_rrhh', 'admin']))
                ->orderByRaw("CASE 
                    WHEN role_id = (SELECT id FROM roles WHERE name = 'admin') THEN 1
                    WHEN role_id = (SELECT id FROM roles WHERE name = 'jefe_rrhh') THEN 2
                    WHEN role_id = (SELECT id FROM roles WHERE name = 'jefe_inmediato') THEN 3
                    ELSE 4 END")
                ->first();
            
            if ($supervisorJefe && $supervisorJefe->id !== $jefe->id) {
                $jefe->update(['immediate_supervisor_id' => $supervisorJefe->id]);
            }
        }
    }

    private function step7_AssignDepartmentManagers()
    {
        $this->info('🎯 PASO 7/7: Asignando Gerentes a Departamentos...');
        
        $departments = Department::all();
        $count = 0;
        
        foreach ($departments as $dept) {
            $manager = User::where('department_id', $dept->id)
                ->whereHas('role', fn($q) => $q->whereIn('name', ['admin', 'jefe_rrhh', 'jefe_inmediato']))
                ->orderByRaw("CASE 
                    WHEN role_id = (SELECT id FROM roles WHERE name = 'admin') THEN 1
                    WHEN role_id = (SELECT id FROM roles WHERE name = 'jefe_rrhh') THEN 2
                    WHEN role_id = (SELECT id FROM roles WHERE name = 'jefe_inmediato') THEN 3
                    ELSE 4 END")
                ->first();
            
            if ($manager) {
                $dept->update(['manager_id' => $manager->id]);
                $count++;
            }
        }
        
        $this->line("   ✓ {$count} gerentes asignados");
    }

    private function generateEmail(string $nombres, string $apellido): string
    {
        $email = strtolower(trim($nombres)) . '.' . strtolower(trim($apellido)) . '@onpe.gob.pe';
        $email = str_replace(' ', '', $email);
        $email = $this->removeAccents($email);
        
        // Verificar si el email ya existe y agregar sufijo
        $baseEmail = $email;
        $counter = 1;
        while (User::where('email', $email)->exists()) {
            $email = str_replace('@', $counter . '@', $baseEmail);
            $counter++;
        }
        
        return $email;
    }

    private function removeAccents(string $string): string
    {
        $unwanted = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
            'ñ' => 'n', 'Ñ' => 'n'
        ];
        
        return strtr($string, $unwanted);
    }

    private function showStatistics()
    {
        $this->newLine();
        $this->info('📊 ESTADÍSTICAS FINALES:');
        
        $totalUsers = User::count();
        $empleados = User::whereHas('role', fn($q) => $q->where('name', 'empleado'))->count();
        $jefesInm = User::whereHas('role', fn($q) => $q->where('name', 'jefe_inmediato'))->count();
        $jefesRrhh = User::whereHas('role', fn($q) => $q->where('name', 'jefe_rrhh'))->count();
        $admins = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->count();
        $conSupervisor = User::whereNotNull('immediate_supervisor_id')->count();
        $sinSupervisor = User::whereNull('immediate_supervisor_id')->count();
        
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Total Usuarios', $totalUsers],
                ['   └─ Empleados', $empleados],
                ['   └─ Jefes Inmediatos', $jefesInm],
                ['   └─ Jefes RRHH', $jefesRrhh],
                ['   └─ Administradores', $admins],
                ['', ''],
                ['Con Supervisor', $conSupervisor],
                ['Sin Supervisor', $sinSupervisor],
                ['', ''],
                ['Total Departamentos', Department::count()],
                ['Tipos de Permiso', PermissionType::count()],
            ]
        );
        
        $this->newLine();
        $this->info('💡 Próximos pasos:');
        $this->line('   1. Revisar usuarios sin supervisor (deberían ser solo Alcalde/Gerentes top)');
        $this->line('   2. Verificar asignación de roles por cargo');
        $this->line('   3. Los usuarios deben cambiar su password (actual = DNI)');
        $this->line('   4. Configurar notificaciones y flujos de aprobación');
    }
}