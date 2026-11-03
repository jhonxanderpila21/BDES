<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Maintenance;
use Illuminate\Database\Seeder;

class MaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gen002 = Asset::where('code', 'GEN-002')->first();
        $saw001 = Asset::where('code', 'SAW-001')->first();
        $bot001 = Asset::where('code', 'BOT-001')->first();
        $veh001 = Asset::where('code', 'VEH-001')->first();

        $maintenances = [
            [
                'asset_id' => $gen002?->id, // Yamaha 3kVA Generator
                'type' => 'routine',
                'technician' => 'Mang Kanor (In-house)',
                'scheduled_date' => now()->subDays(1),
                'estimated_duration' => '2 days',
                'notes' => 'Regular oil change and spark plug replacement.',
                'status' => 'in_progress',
            ],
            [
                'asset_id' => $saw001?->id, // Stihl Chainsaw
                'type' => 'repair',
                'technician' => 'Hardware City Repair Services',
                'scheduled_date' => now()->subWeeks(2),
                'estimated_duration' => '1 month',
                'notes' => 'Engine stalls. Needs carburetor overhaul and chain sharpening.',
                'status' => 'pending',
            ],
            [
                'asset_id' => $bot001?->id, // Rescue Boat
                'type' => 'preventive',
                'technician' => 'Marine Services Corp.',
                'scheduled_date' => now()->addWeeks(2),
                'estimated_duration' => '1 week',
                'notes' => 'Hull inspection, repainting, and outboard motor tune-up before rainy season.',
                'status' => 'scheduled',
            ],
            [
                'asset_id' => $veh001?->id, // Patrol Multicab
                'type' => 'routine',
                'technician' => 'AutoFix Shop',
                'scheduled_date' => now()->subMonths(3),
                'estimated_duration' => '1 day',
                'notes' => 'Change oil, brake fluid flush, and tire rotation.',
                'status' => 'completed',
            ],
        ];

        foreach ($maintenances as $maintenance) {
            if (!empty($maintenance['asset_id'])) {
                Maintenance::create($maintenance);
            }
        }
    }
}
