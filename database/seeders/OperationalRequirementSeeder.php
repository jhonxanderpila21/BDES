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
        // Define realistic operational requirements mapped to asset types
        $requirementsByType = [
            'Generator Set' => [
                [
                    'requirement_type' => 'Diesel Fuel',
                    'unit'             => 'Liters',
                    'current_level'    => 38.50,
                    'maximum_capacity' => 50.00,
                    'minimum_level'    => 15.00,
                    'description'      => 'Ultra-low sulfur diesel fuel for emergency standby power generation.',
                ],
                [
                    'requirement_type' => 'Engine Oil (15W-40)',
                    'unit'             => 'Liters',
                    'current_level'    => 4.20,
                    'maximum_capacity' => 5.00,
                    'minimum_level'    => 2.00,
                    'description'      => 'Heavy-duty 4-stroke diesel engine lubricant.',
                ],
                [
                    'requirement_type' => 'Radiator Coolant',
                    'unit'             => 'Liters',
                    'current_level'    => 8.00,
                    'maximum_capacity' => 10.00,
                    'minimum_level'    => 3.00,
                    'description'      => 'Ethylene glycol pre-mixed 50/50 radiator coolant.',
                ],
            ],

            'Rescue Boat' => [
                [
                    'requirement_type' => 'Marine Gasoline (Unleaded)',
                    'unit'             => 'Liters',
                    'current_level'    => 45.00,
                    'maximum_capacity' => 60.00,
                    'minimum_level'    => 20.00,
                    'description'      => 'Octane 95 unleaded gasoline for 40HP outboard marine rescue motor.',
                ],
                [
                    'requirement_type' => '2-Stroke Marine Oil (TC-W3)',
                    'unit'             => 'Liters',
                    'current_level'    => 2.00, // Low stock on purpose to test low stock filter
                    'maximum_capacity' => 10.00,
                    'minimum_level'    => 3.00,
                    'description'      => 'Certified TC-W3 2-cycle marine outboard engine lubricant (Below safety threshold).',
                ],
            ],

            'Chainsaw' => [
                [
                    'requirement_type' => '2-Stroke Fuel Premix (50:1)',
                    'unit'             => 'Liters',
                    'current_level'    => 12.00,
                    'maximum_capacity' => 15.00,
                    'minimum_level'    => 5.00,
                    'description'      => 'Premixed gasoline and 2T synthetic oil for emergency debris clearing chainsaws.',
                ],
                [
                    'requirement_type' => 'Bar and Chain Oil',
                    'unit'             => 'Liters',
                    'current_level'    => 7.50,
                    'maximum_capacity' => 10.00,
                    'minimum_level'    => 3.00,
                    'description'      => 'High-tack bar and chain adhesive oil for guide bar cutter lubrication.',
                ],
            ],

            'Multi-cab / Patrol Vehicle' => [
                [
                    'requirement_type' => 'Automotive Diesel',
                    'unit'             => 'Liters',
                    'current_level'    => 40.00,
                    'maximum_capacity' => 45.00,
                    'minimum_level'    => 15.00,
                    'description'      => 'Automotive diesel fuel for barangay emergency patrol and evacuation vehicle.',
                ],
                [
                    'requirement_type' => 'Brake Fluid (DOT 4)',
                    'unit'             => 'Liters',
                    'current_level'    => 1.80,
                    'maximum_capacity' => 2.00,
                    'minimum_level'    => 0.80,
                    'description'      => 'High-temperature DOT 4 hydraulic brake and clutch fluid.',
                ],
            ],

            'Defibrillator (AED)' => [
                [
                    'requirement_type' => 'Replacement Lithium Battery Pack',
                    'unit'             => 'Units',
                    'current_level'    => 1.00, // Low stock on purpose
                    'maximum_capacity' => 4.00,
                    'minimum_level'    => 2.00,
                    'description'      => 'Medical-grade non-rechargeable lithium manganese dioxide battery pack for AED defibrillator.',
                ],
            ],
        ];

        foreach ($requirementsByType as $typeName => $requirements) {
            $assetType = AssetType::where('name', $typeName)->first();

            if (!$assetType) {
                continue;
            }

            // Ensure the asset type has the requires_operational_requirements flag set to true
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

