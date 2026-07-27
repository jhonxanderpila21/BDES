<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Safety First Inc.',
                'contact_person' => 'Juan Dela Cruz',
                'phone' => '0917-123-4567',
                'email' => 'sales@safetyfirst.ph',
                'address' => 'Makati City, Metro Manila',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'MediCorp Supplies',
                'contact_person' => 'Maria Santos',
                'phone' => '0918-987-6543',
                'email' => 'info@medicorp.ph',
                'address' => 'Quezon City, Metro Manila',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Heavy Duty Rescues Ltd.',
                'contact_person' => 'Pedro Reyes',
                'phone' => '0919-555-8888',
                'email' => 'pedro@hdr.com',
                'address' => 'Cebu City',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CommTech Solutions',
                'contact_person' => 'Ana Lim',
                'phone' => '0920-111-2222',
                'email' => 'sales@commtech.ph',
                'address' => 'Davao City',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('suppliers')->insert($suppliers);
    }
}
