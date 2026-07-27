<?php

namespace Database\Seeders;

use App\Models\AssetType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssetTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            // Emergency Equipment (category_id 1)
            [
                'category_id' => 1,
                'name' => 'Fire Extinguisher',
                'serialized' => true,
                'borrowable' => true,
                'requires_maintenance' => true,
                'requires_inspection' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => 1,
                'name' => 'Generator Set',
                'serialized' => true,
                'borrowable' => true,
                'requires_maintenance' => true,
                'requires_inspection' => true,
                'has_accessories' => true,
                'requires_operational_requirements' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Medical Supplies (category_id 2)
            [
                'category_id' => 2,
                'name' => 'First Aid Kit (Trauma)',
                'serialized' => false,
                'borrowable' => true,
                'requires_maintenance' => false,
                'requires_inspection' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => 2,
                'name' => 'Defibrillator (AED)',
                'serialized' => true,
                'borrowable' => true,
                'requires_maintenance' => true,
                'requires_inspection' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => 2,
                'name' => 'Stretcher',
                'serialized' => true,
                'borrowable' => true,
                'requires_maintenance' => true,
                'requires_inspection' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Rescue Equipment (category_id 3)
            [
                'category_id' => 3,
                'name' => 'Rescue Rope (100m)',
                'serialized' => true,
                'borrowable' => true,
                'requires_maintenance' => false,
                'requires_inspection' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => 3,
                'name' => 'Chainsaw',
                'serialized' => true,
                'borrowable' => true,
                'requires_maintenance' => true,
                'requires_inspection' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Communication Devices (category_id 4)
            [
                'category_id' => 4,
                'name' => 'Handheld Radio (VHF)',
                'serialized' => true,
                'borrowable' => true,
                'requires_maintenance' => true,
                'requires_inspection' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => 4,
                'name' => 'Megaphone',
                'serialized' => true,
                'borrowable' => true,
                'requires_maintenance' => false,
                'requires_inspection' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Transportation (category_id 5)
            [
                'category_id' => 5,
                'name' => 'Rescue Boat',
                'serialized' => true,
                'borrowable' => false,
                'requires_maintenance' => true,
                'requires_inspection' => true,
                'requires_operational_requirements' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => 5,
                'name' => 'Multi-cab / Patrol Vehicle',
                'serialized' => true,
                'borrowable' => false,
                'requires_maintenance' => true,
                'requires_inspection' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($types as $type) {
            AssetType::create($type);
        }
    }
}
