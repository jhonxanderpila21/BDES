<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetReturn;
use App\Models\Deployment;
use Illuminate\Database\Seeder;

class DeploymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
            [
                'asset_code'           => 'STR-001',
                'fallback_id'          => 7,
                'destination'          => 'Barangay Plaza (Sports Event)',
                'assigned_team'        => 'Alpha Rescue Team',
                'responsible_person'   => 'Team Leader Garcia',
                'borrower_type'        => 'personnel',
                'deployment_date'      => now()->subDays(2),
                'expected_return_date' => now()->addDays(1),
                'purpose'              => 'Medical standby for inter-barangay sports league.',
                'status'               => 'active',
            ],
            [
                'asset_code'           => 'RAD-002',
                'fallback_id'          => 11,
                'destination'          => 'Zone 3 & 4 Outpost',
                'assigned_team'        => 'Tanod Night Watch',
                'responsible_person'   => 'Chief Tanod Ramos',
                'borrower_type'        => 'personnel',
                'deployment_date'      => now()->subHours(8),
                'expected_return_date' => now()->addHours(4),
                'purpose'              => 'Communications patrol monitoring.',
                'status'               => 'active',
            ],
            [
                'asset_code'           => 'FE-002',
                'fallback_id'          => 2,
                'destination'          => 'Purok 4 Community Center',
                'assigned_team'        => 'Resident Borrower',
                'responsible_person'   => 'Maria Teresa Santos',
                'borrower_type'        => 'resident',
                'borrower_name'        => 'Maria Teresa Santos',
                'borrower_contact'     => '09171234567',
                'borrower_address'     => 'House #42, Purok 4, Riverside Street',
                'borrower_id_type'     => 'PhilSys National ID',
                'borrower_id_number'   => '9876-5432-1098-7654',
                'deployment_date'      => now()->subDays(1),
                'expected_return_date' => now()->addDays(2),
                'purpose'              => 'Fire safety standby during Purok fiesta cooking event.',
                'status'               => 'active',
            ],
            [
                'asset_code'           => 'GEN-001',
                'fallback_id'          => 3,
                'destination'          => 'Purok C Evacuation Center',
                'assigned_team'        => 'Logistics Team',
                'responsible_person'   => 'Kagawad Fernandez',
                'borrower_type'        => 'personnel',
                'deployment_date'      => now()->subMonths(1),
                'expected_return_date' => now()->subDays(25),
                'purpose'              => 'Emergency power supply during typhoon evacuation.',
                'status'               => 'returned',
                'return_data'          => [
                    'returned_by'         => 'Logistics Team',
                    'return_date'         => now()->subDays(24),
                    'condition'           => 'good',
                    'damage_notes'        => 'Operated normally with minor exterior dust.',
                    'missing_accessories' => null,
                    'actual_location'     => 'Barangay Hall Storage',
                    'status'              => 'received',
                ],
            ],
            [
                'asset_code'           => 'RAD-002',
                'fallback_id'          => 10,
                'destination'          => 'Purok 1 Day Care Center',
                'assigned_team'        => 'Resident Borrower',
                'responsible_person'   => 'Juan Carlos Dela Cruz',
                'borrower_type'        => 'resident',
                'borrower_name'        => 'Juan Carlos Dela Cruz',
                'borrower_contact'     => '09289876543',
                'borrower_address'     => 'Block 2 Lot 15, Villa San Roque',
                'borrower_id_type'     => "Driver's License",
                'borrower_id_number'   => 'N02-18-987654',
                'deployment_date'      => now()->subDays(5),
                'expected_return_date' => now()->subDays(3),
                'purpose'              => 'Community disaster drill communications liaison.',
                'status'               => 'returned',
                'return_data'          => [
                    'returned_by'         => 'Juan Carlos Dela Cruz',
                    'return_date'         => now()->subDays(3),
                    'condition'           => 'good',
                    'damage_notes'        => 'All bandage rolls returned intact.',
                    'missing_accessories' => null,
                    'actual_location'     => 'Barangay Health Station',
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
                $returnData['asset_id'] = $asset->id;
                AssetReturn::create($returnData);
            }
        }
    }
}

