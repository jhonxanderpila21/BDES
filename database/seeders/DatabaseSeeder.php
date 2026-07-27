<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create or update Administrator User
        User::updateOrCreate(
            ['email' => 'admin@bdes.gov'],
            [
                'name' => 'Admin User',
                'username' => 'admin',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'role' => 'administrator',
                'is_active' => true,
            ]
        );

        // Create or update Viewer User
        User::updateOrCreate(
            ['email' => 'staff@bdes.gov'],
            [
                'name' => 'Staff Member',
                'username' => 'staff',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'role' => 'viewer',
                'is_active' => true,
            ]
        );

        // Call Seeders in dependency order
        $this->call([
            AssetCategorySeeder::class,
            AssetTypeSeeder::class,
            LocationSeeder::class,
            SupplierSeeder::class,
            AssetSeeder::class,
            DeploymentSeeder::class,
            MaintenanceSeeder::class,
            InspectionSeeder::class,
        ]);
    }
}
