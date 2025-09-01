<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PermissionType;

class PermissionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionTypes = [
            // PERMISOS CON GOCE DE REMUNERACIONES
            [
                'name' => 'Por enfermedad',
                'code' => PermissionType::ENFERMEDAD,
                'description' => 'Permiso por enfermedad con certificado médico',
                'max_hours_per_day' => 4,
                'max_hours_per_month' => null,
                'max_times_per_month' => null,
                'requires_document' => true,
                'with_pay' => true,
                'validation_rules' => [
                    'required_documents' => ['certificado_medico'],
                    'max_hours_health' => 4,
                ],
            ],
            [
                'name' => 'Por estado de gravidez',
                'code' => PermissionType::GRAVIDEZ,
                'description' => 'Control mensual por estado de gravidez',
                'max_hours_per_day' => null,
                'max_hours_per_month' => null,
                'max_times_per_month' => 1,
                'requires_document' => true,
                'with_pay' => true,
                'validation_rules' => [
                    'required_documents' => ['certificado_medico'],
                    'max_times_per_month' => 1,
                ],
            ],
            [
                'name' => 'Por capacitación laboral',
                'code' => PermissionType::CAPACITACION,
                'description' => 'Capacitación relacionada con las funciones del servidor',
                'max_hours_per_day' => null,
                'max_hours_per_month' => null,
                'max_times_per_month' => null,
                'requires_document' => true,
                'with_pay' => true,
                'validation_rules' => [
                    'required_approval' => true,
                ],
            ],
            [
                'name' => 'Por citación expresa',
                'code' => PermissionType::CITACION,
                'description' => 'Citación judicial, militar o policial',
                'max_hours_per_day' => null,
                'max_hours_per_month' => null,
                'max_times_per_month' => null,
                'requires_document' => true,
                'with_pay' => true,
                'validation_rules' => [
                    'required_documents' => ['citacion'],
                ],
            ],
            [
                'name' => 'Por función edil',
                'code' => PermissionType::FUNCION_EDIL,
                'description' => 'Ejercicio de función edil',
                'max_hours_per_day' => null,
                'max_hours_per_month' => null,
                'max_times_per_month' => null,
                'requires_document' => true,
                'with_pay' => true,
                'validation_rules' => [
                    'required_documents' => ['acreditacion'],
                ],
            ],
            [
                'name' => 'A cuenta del período vacacional',
                'code' => PermissionType::VACACIONAL,
                'description' => 'Permiso a cuenta del período vacacional pendiente',
                'max_hours_per_day' => null,
                'max_hours_per_month' => null,
                'max_times_per_month' => 3,
                'requires_document' => false,
                'with_pay' => true,
                'validation_rules' => [
                    'max_times_per_month' => 3,
                    'valid_reasons' => ['situacion_familiar', 'accidente', 'salud_familiares'],
                ],
            ],
            [
                'name' => 'Por representación cultural y deportiva',
                'code' => PermissionType::REPRESENTACION,
                'description' => 'Representación cultural o deportiva de la institución',
                'max_hours_per_day' => null,
                'max_hours_per_month' => null,
                'max_times_per_month' => null,
                'requires_document' => true,
                'with_pay' => true,
                'validation_rules' => [
                    'required_approval' => true,
                ],
            ],
            [
                'name' => 'Por ejercicio de docencia universitaria',
                'code' => PermissionType::DOCENCIA,
                'description' => 'Ejercicio de docencia universitaria',
                'max_hours_per_day' => null,
                'max_hours_per_month' => null,
                'max_times_per_month' => null,
                'requires_document' => true,
                'with_pay' => true,
                'validation_rules' => [
                    'max_hours_per_week' => 6,
                    'required_documents' => ['resolucion_nombramiento', 'horario_ensenanza'],
                    'requires_compensation' => true,
                ],
            ],
            [
                'name' => 'Por seguir estudios universitarios',
                'code' => PermissionType::ESTUDIOS,
                'description' => 'Para seguir estudios universitarios',
                'max_hours_per_day' => null,
                'max_hours_per_month' => null,
                'max_times_per_month' => null,
                'requires_document' => true,
                'with_pay' => true,
                'validation_rules' => [
                    'required_documents' => ['resolucion_nombramiento', 'horario_recuperacion'],
                    'requires_compensation' => true,
                ],
            ],
            [
                'name' => 'Por representatividad sindical',
                'code' => PermissionType::SINDICAL,
                'description' => 'Funciones sindicales específicas',
                'max_hours_per_day' => null,
                'max_hours_per_month' => null,
                'max_times_per_month' => null,
                'requires_document' => true,
                'with_pay' => true,
                'validation_rules' => [
                    'max_days_per_year' => 30,
                ],
            ],
            [
                'name' => 'Por lactancia',
                'code' => PermissionType::LACTANCIA,
                'description' => 'Permiso por lactancia hasta que el menor cumpla un año',
                'max_hours_per_day' => 1,
                'max_hours_per_month' => null,
                'max_times_per_month' => null,
                'requires_document' => true,
                'with_pay' => true,
                'validation_rules' => [
                    'required_documents' => ['partida_nacimiento', 'declaracion_jurada'],
                    'max_hours_per_day' => 1,
                    'max_child_age_months' => 12,
                ],
            ],
            [
                'name' => 'Por comisión de servicios',
                'code' => PermissionType::COMISION,
                'description' => 'Comisión de servicios oficial',
                'max_hours_per_day' => null,
                'max_hours_per_month' => null,
                'max_times_per_month' => null,
                'requires_document' => false,
                'with_pay' => true,
                'validation_rules' => [
                    'requires_verification' => true,
                ],
            ],

            // PERMISOS SIN GOCE DE REMUNERACIONES
            [
                'name' => 'Por asuntos particulares',
                'code' => PermissionType::ASUNTOS_PARTICULARES,
                'description' => 'Permiso sin goce por asuntos particulares',
                'max_hours_per_day' => 2,
                'max_hours_per_month' => 6,
                'max_times_per_month' => null,
                'requires_document' => false,
                'with_pay' => false,
                'validation_rules' => [
                    'max_hours_per_day' => 2,
                    'max_hours_per_month' => 6,
                ],
            ],
        ];

        foreach ($permissionTypes as $typeData) {
            PermissionType::create($typeData);
        }
    }
}