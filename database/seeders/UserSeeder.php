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
                'username'          => 'admin',
                'email'             => 'admin@bdes.gov',
                'role'              => 'administrator',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Hon. Barangay Captain',
                'username'          => 'captain',
                'email'             => 'captain@bdes.gov',
                'role'              => 'barangay_captain',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ],

            // ==========================================
            // BDRRMC OPERATIONAL STAFF & OFFICERS (Member)
            // ==========================================
            [
                'name'              => 'BDRRMC Officer Member',
                'username'          => 'member',
                'email'             => 'member@bdes.gov',
                'role'              => 'bdrrmc_officer',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Inventory & Logistics Officer',
                'username'          => 'inventory',
                'email'             => 'inventory@bdes.gov',
                'role'              => 'inventory_officer',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Maintenance Specialist',
                'username'          => 'maintenance',
                'email'             => 'maintenance@bdes.gov',
                'role'              => 'maintenance_officer',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Barangay Staff Member',
                'username'          => 'staff',
                'email'             => 'staff@bdes.gov',
                'role'              => 'viewer',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}

