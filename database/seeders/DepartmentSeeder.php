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
                'description' => 'Despacho de Alcaldía',
                'parent_department_id' => null,
            ],
            [
                'name' => 'Gerencia Municipal',
                'code' => 'GM',
                'description' => 'Gerencia Municipal',
                'parent_department_id' => null,
            ],
            
            // Nivel 2 - Sub-gerencias bajo Gerencia Municipal
            [
                'name' => 'Sub Gerencia de Administración y Finanzas',
                'code' => 'SGAF',
                'description' => 'Administración y Finanzas',
                'parent_department_id' => 2, // Gerencia Municipal
            ],
            [
                'name' => 'Sub Gerencia de Desarrollo Urbano',
                'code' => 'SGDU',
                'description' => 'Desarrollo Urbano y Catastro',
                'parent_department_id' => 2,
            ],
            [
                'name' => 'Sub Gerencia de Servicios Públicos',
                'code' => 'SGSP',
                'description' => 'Servicios Públicos y Medio Ambiente',
                'parent_department_id' => 2,
            ],
            [
                'name' => 'Sub Gerencia de Desarrollo Social',
                'code' => 'SGDS',
                'description' => 'Desarrollo Social y Humano',
                'parent_department_id' => 2,
            ],
            
            // Nivel 3 - Unidades bajo Sub Gerencia de Administración
            [
                'name' => 'Unidad de Recursos Humanos',
                'code' => 'URH',
                'description' => 'Gestión de Recursos Humanos',
                'parent_department_id' => 3, // Sub Gerencia de Administración
            ],
            [
                'name' => 'Unidad de Contabilidad',
                'code' => 'UC',
                'description' => 'Contabilidad y Finanzas',
                'parent_department_id' => 3,
            ],
            [
                'name' => 'Unidad de Tesorería',
                'code' => 'UT',
                'description' => 'Tesorería',
                'parent_department_id' => 3,
            ],
            [
                'name' => 'Unidad de Logística',
                'code' => 'UL',
                'description' => 'Logística y Abastecimiento',
                'parent_department_id' => 3,
            ],
            [
                'name' => 'Unidad de Tecnologías de la Información',
                'code' => 'UTI',
                'description' => 'Sistemas y Tecnología',
                'parent_department_id' => 3,
            ],
            
            // Nivel 3 - Unidades bajo otras Sub Gerencias
            [
                'name' => 'Unidad de Obras Públicas',
                'code' => 'UOP',
                'description' => 'Obras Públicas',
                'parent_department_id' => 4, // Sub Gerencia de Desarrollo Urbano
            ],
            [
                'name' => 'Unidad de Catastro',
                'code' => 'UCAT',
                'description' => 'Catastro Municipal',
                'parent_department_id' => 4,
            ],
            [
                'name' => 'Unidad de Limpieza Pública',
                'code' => 'ULP',
                'description' => 'Limpieza Pública',
                'parent_department_id' => 5, // Sub Gerencia de Servicios Públicos
            ],
            [
                'name' => 'Unidad de Áreas Verdes',
                'code' => 'UAV',
                'description' => 'Parques y Jardines',
                'parent_department_id' => 5,
            ],
            [
                'name' => 'Unidad de Seguridad Ciudadana',
                'code' => 'USC',
                'description' => 'Seguridad Ciudadana',
                'parent_department_id' => 5,
            ],
            [
                'name' => 'Unidad de Programas Sociales',
                'code' => 'UPS',
                'description' => 'Programas Sociales',
                'parent_department_id' => 6, // Sub Gerencia de Desarrollo Social
            ],
            [
                'name' => 'Unidad de Educación, Cultura y Deporte',
                'code' => 'UECD',
                'description' => 'Educación, Cultura y Deporte',
                'parent_department_id' => 6,
            ],
        ];

        foreach ($departments as $departmentData) {
            Department::create($departmentData);
        }
    }
}