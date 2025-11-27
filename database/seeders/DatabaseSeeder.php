<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // El orden es importante debido a las relaciones foreign key
        $this->call([
            RoleSeeder::class,
            PermissionTypeSeeder::class,
            DepartmentSeeder::class, // <--- AGREGADO AQUÍ (antes de UserSeeder)
            UserSeeder::class,
            PermissionTypeOtrosSeeder::class,
        ]);
    }
}