<?php

namespace Database\Seeders;

use App\Models\Deployment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeploymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deployments = [
            [
                'asset_id' => 7, // Folding Stretcher A
                'destination' => 'Barangay Plaza (Sports Event)',
                'assigned_team' => 'Alpha Rescue Team',
                'deployment_date' => now()->subDays(2),
                'expected_return_date' => now()->addDays(1),
                'purpose' => 'Medical standby for inter-barangay basketball league.',
                'status' => 'active',
            ],
            [
                'code' => 'VEH-001',
                'asset_id' => 14, // Barangay Patrol Multicab
                'destination' => 'Zone 3 & 4',
                'assigned_team' => 'Tanod Night Watch',
                'deployment_date' => now()->subHours(8),
                'expected_return_date' => now()->addHours(4),
                'purpose' => 'Routine night patrol.',
                'status' => 'active',
            ],
            [
                'asset_id' => 11, // VHF Radio 2
                'destination' => 'Zone 3 & 4',
                'assigned_team' => 'Tanod Night Watch',
                'deployment_date' => now()->subHours(8),
                'expected_return_date' => now()->addHours(4),
                'purpose' => 'Communications for night patrol.',
                'status' => 'active',
            ],
            [
                'asset_id' => 15, // Trauma Bag 2
                'destination' => 'Zone 3 & 4',
                'assigned_team' => 'Tanod Night Watch',
                'deployment_date' => now()->subHours(8),
                'expected_return_date' => now()->addHours(4),
                'purpose' => 'Medical kit for night patrol vehicle.',
                'status' => 'active',
            ],
            [
                'asset_id' => 3, // Honda 5kVA Generator
                'destination' => 'Purok C Evacuation Center',
                'assigned_team' => 'Logistics Team',
                'deployment_date' => now()->subMonths(1),
                'expected_return_date' => now()->subDays(25),
                'purpose' => 'Power supply during typhoon evacuation.',
                'status' => 'returned',
            ],
        ];

        foreach ($deployments as $deployment) {
            // Remove 'code' key if accidentally added in array above (it's not in Deployment model)
            if (isset($deployment['code'])) {
                unset($deployment['code']);
            }
            Deployment::create($deployment);
        }
    }
}
