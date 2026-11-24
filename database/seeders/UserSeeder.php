<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds default accounts for Admin, Inventory & Maintenance, and Staff (Viewing) roles.
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
                'username'          => '09171000001',
                'email'             => 'admin@bdes.gov',
                'phone_number'      => '09171000001',
                'phone_verified_at' => now(),
                'role'              => 'administrator',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ],

            // ==========================================
            // 2. INVENTORY & MAINTENANCE ROLE (Unified Operations, Deployments, Repairs, Inspections)
            // ==========================================
            [
                'name'              => 'Inventory & Maintenance Officer',
                'first_name'        => 'Inventory',
                'last_name'         => 'Officer',
                'username'          => '09171000002',
                'email'             => 'inventory@bdes.gov',
                'phone_number'      => '09171000002',
                'phone_verified_at' => now(),
                'role'              => 'inventory_officer',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ],

            // ==========================================
            // 3. STAFF FOR VIEWING ROLE (Read-Only)
            // ==========================================
            [
                'name'              => 'Barangay Staff Member',
                'first_name'        => 'Barangay',
                'last_name'         => 'Staff',
                'username'          => '09171000003',
                'email'             => 'staff@bdes.gov',
                'phone_number'      => '09171000003',
                'phone_verified_at' => now(),
                'role'              => 'viewer',
                'password'          => Hash::make('password'),
                'is_active'         => true,
                'email_verified_at' => now(),
            ],
        ];

        // Migrate any legacy or separate maintenance roles into the unified inventory_officer role
        User::withTrashed()
            ->whereIn('role', ['bdrrmc_officer', 'maintenance_officer'])
            ->update(['role' => 'inventory_officer']);
        User::withTrashed()
            ->where('role', 'barangay_captain')
            ->update(['role' => 'administrator']);

        // Remove legacy separate maintenance@bdes.gov account if present
        User::withTrashed()->where('email', 'maintenance@bdes.gov')->forceDelete();

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
