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
            // ADMINISTRATOR & EXECUTIVE ACCOUNTS (Admin)
            // ==========================================
            [
                'name'              => 'System Administrator',
                'first_name'         => 'System',
                'last_name'          => 'Administrator',
                'username'          => 'admin',
                'email'             => 'admin@bdes.gov',
                'role'              => 'administrator',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Hon. Barangay Captain',
                'first_name'         => 'Barangay',
                'last_name'          => 'Captain',
                'username'          => 'captain',
                'email'             => 'captain@bdes.gov',
                'role'              => 'barangay_captain',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ],

            // ==========================================
            // OPERATIONAL STAFF & OFFICERS (Member Roles)
            // ==========================================
            [
                'name'              => 'BDRRMC Inventory Officer',
                'first_name'         => 'Inventory',
                'last_name'          => 'Officer',
                'username'          => 'inventory',
                'email'             => 'inventory@bdes.gov',
                'role'              => 'inventory_officer',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'BDRRMC Operational Officer',
                'first_name'         => 'Operational',
                'last_name'          => 'Officer',
                'username'          => 'member',
                'email'             => 'member@bdes.gov',
                'role'              => 'inventory_officer',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Maintenance Specialist Officer',
                'first_name'         => 'Maintenance',
                'last_name'          => 'Officer',
                'username'          => 'maintenance',
                'email'             => 'maintenance@bdes.gov',
                'role'              => 'maintenance_officer',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Barangay Staff Member',
                'first_name'         => 'Barangay',
                'last_name'          => 'Staff',
                'username'          => 'staff',
                'email'             => 'staff@bdes.gov',
                'role'              => 'viewer',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ],
        ];

        // Migrate any legacy bdrrmc_officer roles to inventory_officer
        User::where('role', 'bdrrmc_officer')->update(['role' => 'inventory_officer']);

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}

