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
            UserSeeder::class,
            PermissionTypeOtrosSeeder::class,
        ]);
    }
}