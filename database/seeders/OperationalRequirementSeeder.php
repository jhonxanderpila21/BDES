<?php

namespace Database\Seeders;

use App\Models\AssetType;
use App\Models\OperationalRequirement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OperationalRequirementSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds for operational requirements.
     */
    public function run(): void
    {
        $requirementsByType = [
            'Fire Truck' => [
                [
                    'requirement_type' => 'Automotive Diesel Fuel (Tanker & Penetrator)',
                    'unit'             => 'Liters',
                    'current_level'    => 180.00,
                    'maximum_capacity' => 250.00,
                    'minimum_level'    => 80.00,
                    'description'      => 'Diesel fuel reserve for Fire Truck – Tanker and Fire Truck – Penetrator apparatus.',
                ],
                [
                    'requirement_type' => 'Aqueous Film-Forming Foam (AFFF 3%)',
                    'unit'             => 'Liters',
                    'current_level'    => 25.00, // Low stock on purpose to highlight low-stock alerts
                    'maximum_capacity' => 100.00,
                    'minimum_level'    => 40.00,
                    'description'      => 'Fire suppression foam concentrate for Tanker and Penetrator 1½-inch and 2½-inch hose lines.',
                ],
                [
                    'requirement_type' => 'Heavy-Duty Diesel Engine Oil (15W-40)',
                    'unit'             => 'Liters',
                    'current_level'    => 16.00,
                    'maximum_capacity' => 20.00,
                    'minimum_level'    => 6.00,
                    'description'      => 'Engine and PTO fire pump lubricant for Tanker and Penetrator fire trucks.',
                ],
            ],

            'Chainsaw' => [
                [
                    'requirement_type' => '2-Stroke Fuel Premix (50:1)',
                    'unit'             => 'Liters',
                    'current_level'    => 14.00,
                    'maximum_capacity' => 20.00,
                    'minimum_level'    => 6.00,
                    'description'      => 'Premixed gasoline and 2T synthetic oil for the 4 chainsaws (2 large, 2 small).',
                ],
                [
                    'requirement_type' => 'Bar and Chain Oil',
                    'unit'             => 'Liters',
                    'current_level'    => 2.50, // Low stock on purpose
                    'maximum_capacity' => 12.00,
                    'minimum_level'    => 4.00,
                    'description'      => 'High-tack bar and chain adhesive oil for the 2 large and 2 small chainsaws.',
                ],
            ],

            'Hydraulic & Lifting Tool' => [
                [
                    'requirement_type' => 'ISO VG 32 Hydraulic Jack Oil',
                    'unit'             => 'Liters',
                    'current_level'    => 4.00,
                    'maximum_capacity' => 5.00,
                    'minimum_level'    => 1.50,
                    'description'      => 'Anti-wear hydraulic fluid for the 3 heavy-duty rescue jacks.',
                ],
            ],

            'Generator Set' => [
                [
                    'requirement_type' => 'Diesel Fuel',
                    'unit'             => 'Liters',
                    'current_level'    => 38.50,
                    'maximum_capacity' => 50.00,
                    'minimum_level'    => 15.00,
                    'description'      => 'Ultra-low sulfur diesel fuel for fire station emergency standby power generation.',
                ],
                [
                    'requirement_type' => 'Engine Oil (15W-40)',
                    'unit'             => 'Liters',
                    'current_level'    => 4.20,
                    'maximum_capacity' => 5.00,
                    'minimum_level'    => 2.00,
                    'description'      => 'Heavy-duty 4-stroke engine lubricant.',
                ],
            ],

            'Rescue Boat' => [
                [
                    'requirement_type' => 'Marine Gasoline (Unleaded)',
                    'unit'             => 'Liters',
                    'current_level'    => 45.00,
                    'maximum_capacity' => 60.00,
                    'minimum_level'    => 20.00,
                    'description'      => 'Octane 95 unleaded gasoline for outboard marine rescue motor.',
                ],
            ],
        ];

        foreach ($requirementsByType as $typeName => $requirements) {
            $assetType = AssetType::where('name', $typeName)->first();

            if (!$assetType) {
                continue;
            }

            if (!$assetType->requires_operational_requirements) {
                $assetType->update(['requires_operational_requirements' => true]);
            }

            foreach ($requirements as $req) {
                OperationalRequirement::updateOrCreate(
                    [
                        'asset_type_id'    => $assetType->id,
                        'requirement_type' => $req['requirement_type'],
                    ],
                    [
                        'unit'             => $req['unit'],
                        'current_level'    => $req['current_level'],
                        'maximum_capacity' => $req['maximum_capacity'],
                        'minimum_level'    => $req['minimum_level'],
                        'description'      => $req['description'],
                    ]
                );
            }
        }
    }
}
