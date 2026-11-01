<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Inspection;
use Illuminate\Database\Seeder;

class InspectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fe001  = Asset::where('code', 'FE-001')->first();
        $aed001 = Asset::where('code', 'AED-001')->first();
        $rop001 = Asset::where('code', 'ROP-001')->first();

        $inspections = [
            [
                'asset_id' => $fe001?->id, // Main Hall FE A
                'inspector' => 'BFP Officer Cruz',
                'inspection_date' => now()->subMonths(6),
                'result' => 'passed',
                'findings' => 'Pressure normal. Seal intact.',
                'next_due_date' => now()->addMonths(6),
                'status' => 'completed',
            ],
            [
                'asset_id' => $aed001?->id, // Zoll AED
                'inspector' => 'Dr. Fernandez',
                'inspection_date' => now()->addDays(5),
                'result' => 'passed',
                'findings' => null,
                'next_due_date' => null,
                'status' => 'scheduled',
            ],
            [
                'asset_id' => $rop001?->id, // Rescue Rope
                'inspector' => 'Chief Rescuer Reyes',
                'inspection_date' => now()->subDays(10),
                'result' => 'conditional_pass',
                'findings' => 'Slight fraying at one end. Recommend cutting the damaged 2 meters off.',
                'next_due_date' => now()->addMonths(3),
                'status' => 'completed',
            ],
        ];

        foreach ($inspections as $inspection) {
            if (!empty($inspection['asset_id'])) {
                Inspection::create($inspection);
            }
        }
    }
}
