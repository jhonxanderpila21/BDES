<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds default accounts for both Administrator and BDRRMC Member roles.
     */
    public function run(): void
    {
        $users = [
            // ==========================================
            // 1. ADMIN ROLE (Full Governance & Settings)
            // ==========================================
            [
                'name'              => 'System Administrator',
                'first_name'        => 'System',
                'last_name'         => 'Administrator',
                'username'          => 'admin',
                'email'             => 'admin@bdes.gov',
                'role'              => 'administrator',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ],

            // ==========================================
            // 2. INVENTORY ROLE (Operations, Deployments)
            // ==========================================
            [
                'name'              => 'BDRRMC Inventory Officer',
                'first_name'        => 'Inventory',
                'last_name'         => 'Officer',
                'username'          => 'inventory',
                'email'             => 'inventory@bdes.gov',
                'role'              => 'inventory_officer',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ],

            // ==========================================
            // 3. MAINTENANCE ROLE (Repairs, Inspections)
            // ==========================================
            [
                'name'              => 'Maintenance Officer',
                'first_name'        => 'Maintenance',
                'last_name'         => 'Officer',
                'username'          => 'maintenance',
                'email'             => 'maintenance@bdes.gov',
                'role'              => 'maintenance_officer',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ],

            // ==========================================
            // 4. STAFF FOR VIEWING ROLE (Read-Only)
            // ==========================================
            [
                'name'              => 'Barangay Staff Member',
                'first_name'        => 'Barangay',
                'last_name'         => 'Staff',
                'username'          => 'staff',
                'email'             => 'staff@bdes.gov',
                'role'              => 'viewer',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ],
        ];

        // Migrate any legacy roles to modern 4-tier roles
        User::where('role', 'bdrrmc_officer')->update(['role' => 'inventory_officer']);
        User::where('role', 'barangay_captain')->update(['role' => 'administrator']);

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}

