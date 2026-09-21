<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Emergency Equipment',
                'description' => 'Equipment used for immediate disaster response and emergencies.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Medical Supplies',
                'description' => 'First aid kits, stretchers, medicines, and medical tools.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rescue Equipment',
                'description' => 'Heavy duty tools used for search and rescue operations.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Communication Devices',
                'description' => 'Radios, megaphones, and satellite phones for coordination.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Transportation',
                'description' => 'Vehicles and boats used for evacuation and logistics.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($categories as $category) {
            DB::table('asset_categories')->updateOrInsert(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
