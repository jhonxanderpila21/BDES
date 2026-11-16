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
                'description' => 'Firefighting hoses, nozzles, couplings, fire extinguishers, and emergency power equipment.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Medical Supplies',
                'description' => 'First aid kits, stretchers, defibrillators, and emergency medical tools.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rescue Equipment',
                'description' => 'Chainsaws, hydraulic jacks, bolt cutters, clearing tools (machetes, rakes, ropes), and mechanical hand tools.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Communication Devices',
                'description' => 'Radios, megaphones, and dispatch equipment for fire and rescue coordination.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Transportation',
                'description' => 'Fire trucks (Tanker and Penetrator), rescue boats, and emergency response vehicles.',
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
