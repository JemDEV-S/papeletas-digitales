<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            // Nivel 1 - Departamentos principales
            [
                'name' => 'Alcaldía',
                'code' => 'ALC',
                'description' => 'Despacho de Alcaldía Municipal',
                'parent_department_id' => null,
            ],
            [
                'name' => 'Gerencia Municipal',
                'code' => 'GM',
                'description' => 'Gerencia Municipal Principal',
                'parent_department_id' => null,
            ],
            
            // Nivel 2 - Gerencias especializadas
            [
                'name' => 'Gerencia de Planeamiento y Presupuesto',
                'code' => 'GPP',
                'description' => 'Planeamiento Estratégico y Gestión Presupuestaria',
                'parent_department_id' => 2, // Gerencia Municipal
            ],
            [
                'name' => 'Gerencia de Administración',
                'code' => 'GA',
                'description' => 'Administración General y Recursos',
                'parent_department_id' => 2, // Gerencia Municipal
            ],
            [
                'name' => 'Gerencia de Desarrollo Urbano',
                'code' => 'GDU',
                'description' => 'Desarrollo Urbano y Territorial',
                'parent_department_id' => 2, // Gerencia Municipal
            ],
            [
                'name' => 'Gerencia de Servicios Municipales',
                'code' => 'GS',
                'description' => 'Servicios Públicos Locales',
                'parent_department_id' => 2, // Gerencia Municipal
            ],
            
            // Nivel 3 - Unidades bajo Gerencia de Planeamiento
            [
                'name' => 'Unidad de Planeamiento Estratégico',
                'code' => 'UPE',
                'description' => 'Planificación y Desarrollo Institucional',
                'parent_department_id' => 3, // Gerencia de Planeamiento y Presupuesto
            ],
            [
                'name' => 'Unidad de Presupuesto',
                'code' => 'UP',
                'description' => 'Formulación y Evaluación Presupuestaria',
                'parent_department_id' => 3, // Gerencia de Planeamiento y Presupuesto
            ],
            [
                'name' => 'Unidad de Estadística e Informática',
                'code' => 'UEI',
                'description' => 'Estadística Municipal e Informática',
                'parent_department_id' => 3, // Gerencia de Planeamiento y Presupuesto
            ],
            
            // Nivel 3 - Oficinas especializadas bajo Gerencia de Planeamiento
            [
                'name' => 'Oficina de Tecnologías de la Información',
                'code' => 'OTI',
                'description' => 'Sistemas de Información y Soporte Tecnológico',
                'parent_department_id' => 3, // Gerencia de Planeamiento y Presupuesto
            ],
            
            // Nivel 3 - Unidades bajo Gerencia de Administración
            [
                'name' => 'Unidad de Recursos Humanos',
                'code' => 'URH',
                'description' => 'Gestión del Talento Humano',
                'parent_department_id' => 4, // Gerencia de Administración
            ],
            [
                'name' => 'Unidad de Contabilidad',
                'code' => 'UC',
                'description' => 'Contabilidad y Estados Financieros',
                'parent_department_id' => 4, // Gerencia de Administración
            ],
            [
                'name' => 'Unidad de Tesorería',
                'code' => 'UT',
                'description' => 'Tesorería y Recaudación',
                'parent_department_id' => 4, // Gerencia de Administración
            ],
            [
                'name' => 'Unidad de Logística y Servicios',
                'code' => 'ULS',
                'description' => 'Logística, Abastecimiento y Servicios Generales',
                'parent_department_id' => 4, // Gerencia de Administración
            ],
            [
                'name' => 'Unidad de Control Patrimonial',
                'code' => 'UCP',
                'description' => 'Control y Registro de Bienes Patrimoniales',
                'parent_department_id' => 4, // Gerencia de Administración
            ],
            
            // Nivel 3 - Unidades bajo Gerencia de Desarrollo Urbano
            [
                'name' => 'Unidad de Obras Públicas',
                'code' => 'UOP',
                'description' => 'Ejecución de Obras de Infraestructura',
                'parent_department_id' => 5, // Gerencia de Desarrollo Urbano
            ],
            [
                'name' => 'Unidad de Catastro',
                'code' => 'UCAT',
                'description' => 'Catastro Urbano y Rural',
                'parent_department_id' => 5, // Gerencia de Desarrollo Urbano
            ],
            [
                'name' => 'Unidad de Estudios y Proyectos',
                'code' => 'UEP',
                'description' => 'Formulación de Estudios y Proyectos',
                'parent_department_id' => 5, // Gerencia de Desarrollo Urbano
            ],
            [
                'name' => 'Unidad de Licencias y Habilitaciones',
                'code' => 'ULH',
                'description' => 'Licencias de Construcción y Habilitaciones Urbanas',
                'parent_department_id' => 5, // Gerencia de Desarrollo Urbano
            ],
            
            // Nivel 3 - Unidades bajo Gerencia de Servicios Municipales
            [
                'name' => 'Unidad de Limpieza Pública',
                'code' => 'ULP',
                'description' => 'Recolección de Residuos y Limpieza',
                'parent_department_id' => 6, // Gerencia de Servicios
            ],
            [
                'name' => 'Unidad de Áreas Verdes y Ornato',
                'code' => 'UAVO',
                'description' => 'Mantenimiento de Parques y Jardines',
                'parent_department_id' => 6, // Gerencia de Servicios
            ],
            [
                'name' => 'Unidad de Seguridad Ciudadana',
                'code' => 'USC',
                'description' => 'Serenazgo y Seguridad Ciudadana',
                'parent_department_id' => 6, // Gerencia de Servicios
            ],
            [
                'name' => 'Unidad de Servicios Sociales',
                'code' => 'USS',
                'description' => 'Programas Sociales y Asistencia Social',
                'parent_department_id' => 6, // Gerencia de Servicios
            ],
            [
                'name' => 'Unidad de Educación y Cultura',
                'code' => 'UEC',
                'description' => 'Promoción Educativa y Cultural',
                'parent_department_id' => 6, // Gerencia de Servicios
            ],
            
            // Nivel 4 - Sub-unidades especializadas
            [
                'name' => 'Mesa de Partes',
                'code' => 'MP',
                'description' => 'Atención al Ciudadano y Trámite Documentario',
                'parent_department_id' => 4, // Gerencia de Administración
            ],
            [
                'name' => 'Archivo Municipal',
                'code' => 'AM',
                'description' => 'Gestión Documental y Archivo',
                'parent_department_id' => 4, // Gerencia de Administración
            ],
            [
                'name' => 'Unidad de Defensa Civil',
                'code' => 'UDC',
                'description' => 'Gestión del Riesgo de Desastres',
                'parent_department_id' => 6, // Gerencia de Servicios
            ],
        ];

        foreach ($departments as $departmentData) {
            Department::create($departmentData);
        }

        $this->command->info('Departamentos creados exitosamente:');
        $this->command->info('========================================');
        $this->command->info('Nivel 1: Alcaldía y Gerencia Municipal');
        $this->command->info('Nivel 2: 4 Gerencias especializadas');
        $this->command->info('Nivel 3: Unidades operativas y OTI');
        $this->command->info('Nivel 4: Sub-unidades de apoyo');
        $this->command->info('========================================');
        $this->command->info('Total departamentos: ' . count($departments));
    }
}