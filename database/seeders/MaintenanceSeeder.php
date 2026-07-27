<?php

namespace Database\Seeders;

use App\Models\Maintenance;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $maintenances = [
            [
                'asset_id' => 4, // Yamaha 3kVA Generator
                'type' => 'routine',
                'technician' => 'Mang Kanor (In-house)',
                'scheduled_date' => now()->subDays(1),
                'estimated_duration' => '2 days',
                'notes' => 'Regular oil change and spark plug replacement.',
                'status' => 'in_progress',
            ],
            [
                'asset_id' => 9, // Stihl Chainsaw
                'type' => 'repair',
                'technician' => 'Hardware City Repair Services',
                'scheduled_date' => now()->subWeeks(2),
                'estimated_duration' => '1 month',
                'notes' => 'Engine stalls. Needs carburetor overhaul and chain sharpening.',
                'status' => 'pending',
            ],
            [
                'asset_id' => 13, // Rescue Boat
                'type' => 'preventive',
                'technician' => 'Marine Services Corp.',
                'scheduled_date' => now()->addWeeks(2),
                'estimated_duration' => '1 week',
                'notes' => 'Hull inspection, repainting, and outboard motor tune-up before rainy season.',
                'status' => 'scheduled',
            ],
            [
                'asset_id' => 14, // Patrol Multicab
                'type' => 'routine',
                'technician' => 'AutoFix Shop',
                'scheduled_date' => now()->subMonths(3),
                'estimated_duration' => '1 day',
                'notes' => 'Change oil, brake fluid flush, and tire rotation.',
                'status' => 'completed',
            ],
        ];

        foreach ($maintenances as $maintenance) {
            Maintenance::create($maintenance);
        }
    }
}
