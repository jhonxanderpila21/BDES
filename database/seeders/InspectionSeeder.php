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
        $tanker     = Asset::where('code', 'TRK-001')->first();
        $penetrator = Asset::where('code', 'TRK-002')->first();
        $hose15Tnk  = Asset::where('code', 'FH-001')->first();
        $hose15Dmg  = Asset::where('code', 'FH-005')->first();
        $hose25Dmg  = Asset::where('code', 'FH-006')->first();
        $chainsaw   = Asset::where('code', 'SAW-001')->first();
        $ropes      = Asset::where('code', 'ROP-001')->first();

        $inspections = [
            // Post-return inspection for 1½-inch Fire Hose – 1 (FH-005) -> Failed (Damage Flow)
            [
                'asset_id'        => $hose15Dmg?->id,
                'inspector'       => 'BFP Officer Cruz',
                'inspection_date' => now()->subDays(5),
                'result'          => 'failed',
                'findings'        => 'Hydrostatic test failed at 100 PSI due to punctured inner rubber lining from Purok B fire operation. Flagged Poor & Out of Service; repair work order generated.',
                'next_due_date'   => now()->addDays(7),
                'status'          => 'completed',
            ],
            // Post-return inspection for 2½-inch Fire Hose – 1 (FH-006) -> Failed (Damage Flow)
            [
                'asset_id'        => $hose25Dmg?->id,
                'inspector'       => 'BFP Officer Cruz',
                'inspection_date' => now()->subDays(3),
                'result'          => 'failed',
                'findings'        => 'Outer jacket seam split near female brass coupling confirmed during post-deployment inspection. Flagged Poor & Out of Service; repair work order generated.',
                'next_due_date'   => now()->addDays(7),
                'status'          => 'completed',
            ],
            [
                'asset_id'        => $tanker?->id,
                'inspector'       => 'BFP Officer Cruz',
                'inspection_date' => now()->subDays(15),
                'result'          => 'passed',
                'findings'        => 'Tanker fire pump pressure normal. All 7x 1½-inch hoses, 3x 2½-inch hoses, 1x 2½-inch hose peed, spanner, yabie tubo, vice grip, cutter pliers, and electrical pliers verified onboard.',
                'next_due_date'   => now()->addMonths(3),
                'status'          => 'completed',
            ],
            [
                'asset_id'        => $penetrator?->id,
                'inspector'       => 'BFP Officer Cruz',
                'inspection_date' => now()->subDays(14),
                'result'          => 'passed',
                'findings'        => 'Penetrator apparatus ready for dispatch. All 6x 1½-inch hoses, 2x 2½-inch hoses, 1x 2½-inch hose peed, and truck hand tools complete.',
                'next_due_date'   => now()->addMonths(3),
                'status'          => 'completed',
            ],
            [
                'asset_id'        => $hose15Tnk?->id,
                'inspector'       => 'Fire Marshall Garcia',
                'inspection_date' => now()->addDays(5),
                'result'          => 'passed',
                'findings'        => null,
                'next_due_date'   => null,
                'status'          => 'scheduled',
            ],
            [
                'asset_id'        => $chainsaw?->id,
                'inspector'       => 'Chief Rescuer Reyes',
                'inspection_date' => now()->subDays(7),
                'result'          => 'passed',
                'findings'        => 'All 4 chainsaws (2 large, 2 small) started on first pull; chain brake and oiler functioning properly.',
                'next_due_date'   => now()->addMonths(2),
                'status'          => 'completed',
            ],
            [
                'asset_id'        => $ropes?->id,
                'inspector'       => 'Chief Rescuer Reyes',
                'inspection_date' => now()->subDays(10),
                'result'          => 'conditional_pass',
                'findings'        => '4 Ropes (Pisi) inspected; 3 coils in top condition, 1 coil has minor surface wear near end knot—monitor during heavy hauling.',
                'next_due_date'   => now()->addMonths(3),
                'status'          => 'completed',
            ],
        ];

        foreach ($inspections as $inspection) {
            if (!empty($inspection['asset_id'])) {
                Inspection::create($inspection);
            }
        }
    }
}
