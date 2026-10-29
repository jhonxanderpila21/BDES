<?php

namespace Database\Seeders;

use App\Models\Inspection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InspectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inspections = [
            [
                'asset_id' => 1, // Main Hall FE A
                'inspector' => 'BFP Officer Cruz',
                'inspection_date' => now()->subMonths(6),
                'result' => 'passed',
                'findings' => 'Pressure normal. Seal intact.',
                'next_due_date' => now()->addMonths(6),
                'status' => 'completed',
            ],
            [
                'asset_id' => 6, // Zoll AED
                'inspector' => 'Dr. Fernandez',
                'inspection_date' => now()->addDays(5),
                'result' => 'passed', // Will be ignored by DB structure since it's scheduled, but keeping for completeness
                'findings' => null,
                'next_due_date' => null,
                'status' => 'scheduled',
            ],
            [
                'asset_id' => 8, // Rescue Rope
                'inspector' => 'Chief Rescuer Reyes',
                'inspection_date' => now()->subDays(10),
                'result' => 'conditional_pass',
                'findings' => 'Slight fraying at one end. Recommend cutting the damaged 2 meters off.',
                'next_due_date' => now()->addMonths(3),
                'status' => 'completed',
            ],
        ];

        foreach ($inspections as $inspection) {
            Inspection::create($inspection);
        }
    }
}
