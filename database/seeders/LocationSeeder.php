<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Main Barangay Hall Storage',
                'type' => 'headquarters',
                'address' => 'Barangay Hall Complex, Main Street',
                'storage_capacity' => 1000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Purok A Fire Station',
                'type' => 'outpost',
                'address' => 'Zone 1, Purok A',
                'storage_capacity' => 500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Medical Response Center',
                'type' => 'clinic',
                'address' => 'Barangay Health Center',
                'storage_capacity' => 300,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Evacuation Center Warehouse',
                'type' => 'warehouse',
                'address' => 'Gymnasium Complex',
                'storage_capacity' => 2000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'River Patrol Outpost',
                'type' => 'outpost',
                'address' => 'Riverside Area',
                'storage_capacity' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($locations as $location) {
            DB::table('locations')->updateOrInsert(
                ['name' => $location['name']],
                $location
            );
        }
    }
}
