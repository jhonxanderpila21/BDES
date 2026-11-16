<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetReturn;
use App\Models\Deployment;
use App\Models\User;
use Illuminate\Database\Seeder;

class DeploymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'administrator')->first() ?? User::first();

        $records = [
            [
                'asset_code'           => 'TRK-001',
                'fallback_id'          => 1,
                'destination'          => 'Purok 2 Commercial Zone',
                'assigned_team'        => 'Alpha Fire Suppression Crew',
                'responsible_person'   => 'Fire Marshall Garcia',
                'borrower_type'        => 'personnel',
                'deployment_date'      => now()->subHours(6),
                'expected_return_date' => now()->addHours(6),
                'purpose'              => 'Fire Truck – Tanker (with 7x 1½-inch hoses, 3x 2½-inch hoses, 2½-inch hose peed, and truck tool kit) on fire safety standby.',
                'status'               => 'active',
            ],
            [
                'asset_code'           => 'SAW-001',
                'fallback_id'          => 12,
                'destination'          => 'Zone 3 Highway & Riverside Road',
                'assigned_team'        => 'Bravo Clearing & Rescue Team',
                'responsible_person'   => 'Team Leader Ramos',
                'borrower_type'        => 'personnel',
                'deployment_date'      => now()->subDays(1),
                'expected_return_date' => now()->addDays(1),
                'purpose'              => 'Chainsaws (2 large, 2 small) deployed for clearing fallen trees blocking emergency access road.',
                'status'               => 'active',
            ],
            [
                'asset_code'           => 'RCK-001',
                'fallback_id'          => 15,
                'destination'          => 'Purok 4 Community Canal & Plaza',
                'assigned_team'        => 'Barangay Clean-Up & Clearing Brigade',
                'responsible_person'   => 'Maria Teresa Santos',
                'borrower_type'        => 'resident',
                'borrower_name'        => 'Maria Teresa Santos',
                'borrower_contact'     => '09171234567',
                'borrower_address'     => 'House #42, Purok 4, Riverside Street',
                'borrower_id_type'     => 'PhilSys National ID',
                'borrower_id_number'   => '9876-5432-1098-7654',
                'deployment_date'      => now()->subDays(1),
                'expected_return_date' => now()->addDays(2),
                'purpose'              => '14 Ricks (rakes) and Machetes (Sundang) deployed for post-storm debris and drainage clearing.',
                'status'               => 'active',
            ],
            // System Damage Flow 1: 1½-inch Fire Hose – 1 returned from deployment with damage_noted & poor condition -> transitions asset to out_of_service & poor
            [
                'asset_code'           => 'FH-005',
                'fallback_id'          => 22,
                'destination'          => 'Purok B Residential Fire Incident',
                'assigned_team'        => 'Alpha Fire Suppression Crew',
                'responsible_person'   => 'Fire Marshall Garcia',
                'borrower_type'        => 'personnel',
                'deployment_date'      => now()->subDays(6),
                'expected_return_date' => now()->subDays(5),
                'purpose'              => 'Deployed 1½-inch fire hose line for interior fire attack at Purok B.',
                'status'               => 'returned',
                'return_data'          => [
                    'returned_by'         => 'Fire Marshall Garcia',
                    'received_by'         => $admin?->id,
                    'return_date'         => now()->subDays(5),
                    'condition'           => 'poor',
                    'damage_notes'        => 'Punctured inner rubber lining and abraded outer canvas jacket caused by sharp sheet metal debris during fire suppression.',
                    'missing_accessories' => null,
                    'actual_location'     => 'Purok A Fire Station',
                    'status'              => 'damage_noted',
                ],
            ],
            // System Damage Flow 2: 2½-inch Fire Hose – 1 returned from deployment with damage_noted & poor condition -> transitions asset to out_of_service & poor
            [
                'asset_code'           => 'FH-006',
                'fallback_id'          => 23,
                'destination'          => 'Zone 2 Warehouse Fire Hydrant Relay',
                'assigned_team'        => 'Rapid Intervention Fire Unit',
                'responsible_person'   => 'Kagawad Fernandez',
                'borrower_type'        => 'personnel',
                'deployment_date'      => now()->subDays(4),
                'expected_return_date' => now()->subDays(3),
                'purpose'              => 'High-volume water supply relay from municipal hydrant to Tanker.',
                'status'               => 'returned',
                'return_data'          => [
                    'returned_by'         => 'Kagawad Fernandez',
                    'received_by'         => $admin?->id,
                    'return_date'         => now()->subDays(3),
                    'condition'           => 'poor',
                    'damage_notes'        => 'Outer jacket seam split near female brass coupling under 150 PSI pump surge.',
                    'missing_accessories' => null,
                    'actual_location'     => 'Purok A Fire Station',
                    'status'              => 'damage_noted',
                ],
            ],
            [
                'asset_code'           => 'TRK-002',
                'fallback_id'          => 2,
                'destination'          => 'Purok C Narrow Alley Residential Area',
                'assigned_team'        => 'Rapid Intervention Fire Unit',
                'responsible_person'   => 'Kagawad Fernandez',
                'borrower_type'        => 'personnel',
                'deployment_date'      => now()->subDays(10),
                'expected_return_date' => now()->subDays(9),
                'purpose'              => 'Fire Truck – Penetrator (with 6x 1½-inch hoses and 2x 2½-inch hoses) dispatched for grass fire containment.',
                'status'               => 'returned',
                'return_data'          => [
                    'returned_by'         => 'Rapid Intervention Fire Unit',
                    'received_by'         => $admin?->id,
                    'return_date'         => now()->subDays(9),
                    'condition'           => 'good',
                    'damage_notes'        => 'All 6x 1½-inch hoses, 2x 2½-inch hoses, spanner, yabie tubo, vice grip, cutter pliers, and electrical pliers accounted for and washed.',
                    'missing_accessories' => null,
                    'actual_location'     => 'Purok A Fire Station',
                    'status'              => 'received',
                ],
            ],
            [
                'asset_code'           => 'ROP-001',
                'fallback_id'          => 14,
                'destination'          => 'Purok 1 Riverbank Sector',
                'assigned_team'        => 'Search & Rescue Volunteers',
                'responsible_person'   => 'Juan Carlos Dela Cruz',
                'borrower_type'        => 'resident',
                'borrower_name'        => 'Juan Carlos Dela Cruz',
                'borrower_contact'     => '09289876543',
                'borrower_address'     => 'Block 2 Lot 15, Villa San Roque',
                'borrower_id_type'     => "Driver's License",
                'borrower_id_number'   => 'N02-18-987654',
                'deployment_date'      => now()->subDays(5),
                'expected_return_date' => now()->subDays(3),
                'purpose'              => '4 Ropes (Pisi) and Machetes (Sundang) used for riverbank stabilization and rescue drill.',
                'status'               => 'returned',
                'return_data'          => [
                    'returned_by'         => 'Juan Carlos Dela Cruz',
                    'received_by'         => $admin?->id,
                    'return_date'         => now()->subDays(3),
                    'condition'           => 'good',
                    'damage_notes'        => 'All 4 ropes dried, coiled, and inspected with zero fraying.',
                    'missing_accessories' => null,
                    'actual_location'     => 'Main Barangay Hall Storage',
                    'status'              => 'received',
                ],
            ],
        ];

        foreach ($records as $item) {
            $asset = Asset::where('code', $item['asset_code'])->first()
                ?? Asset::find($item['fallback_id'])
                ?? Asset::first();

            if (!$asset) {
                continue;
            }

            $returnData = $item['return_data'] ?? null;
            unset($item['asset_code'], $item['fallback_id'], $item['return_data']);

            $item['asset_id'] = $asset->id;

            $deployment = Deployment::create($item);

            if ($item['status'] === 'returned' && $returnData) {
                $returnData['deployment_id'] = $deployment->id;
                $returnData['asset_id']      = $asset->id;
                AssetReturn::create($returnData);

                // Mirror the system return workflow in DeploymentController::return()
                if ($returnData['condition'] === 'poor') {
                    $asset->update([
                        'condition' => 'poor',
                        'status'    => 'out_of_service',
                    ]);
                } elseif ($returnData['condition'] === 'fair') {
                    $asset->update([
                        'condition' => 'fair',
                        'status'    => 'under_maintenance',
                    ]);
                }
            }
        }
    }
}
