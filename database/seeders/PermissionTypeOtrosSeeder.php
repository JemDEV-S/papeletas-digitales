<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PermissionType;

class PermissionTypeOtrosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $otrosPermission = [
            'name' => 'Otros',
            'code' => PermissionType::OTROS,
            'description' => 'Permisos especiales que no se encuentran en las categorías establecidas',
            'max_hours_per_day' => null,
            'max_hours_per_month' => null,
            'max_times_per_month' => null,
            'requires_document' => false,
            'with_pay' => false,
            'validation_rules' => [
                'required_approval' => true,
                'observations_required' => true,
            ],
        ];

        PermissionType::firstOrCreate(
            ['code' => $otrosPermission['code']],
            $otrosPermission
        );
    }
}
