<?php

namespace Database\Seeders;

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
            DepartmentSeeder::class,
            PermissionTypeSeeder::class,
            UserSeeder::class,
        ]);
    }
}