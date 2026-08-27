<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\MaintenanceSchedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MaintenanceScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gen001 = Asset::where('code', 'GEN-001')->first();
        $saw001 = Asset::where('code', 'SAW-001')->first();
        $bot001 = Asset::where('code', 'BOT-001')->first();
        $veh001 = Asset::where('code', 'VEH-001')->first();

        $schedules = [
            // Generator / Oil Change / Monthly (30 days) / last done 10 days ago → Monitoring Only
            [
                'asset_id' => $gen001?->id,
                'asset_type' => 'Generator',
                'maintenance_task' => 'Oil Change',
                'interval_type' => 'Monthly',
                'interval_days' => 30,
                'last_maintenance_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
            ],
            // Generator / Spark Plug Check / Quarterly (90 days) / last done 88 days ago → Monitoring Only (but close to due)
            [
                'asset_id' => $gen001?->id,
                'asset_type' => 'Generator',
                'maintenance_task' => 'Spark Plug Check',
                'interval_type' => 'Quarterly',
                'interval_days' => 90,
                'last_maintenance_date' => Carbon::now()->subDays(88)->format('Y-m-d'),
            ],
            // Chainsaw / Blade Sharpening / Quarterly (90 days) / last done 95 days ago → Maintenance Due
            [
                'asset_id' => $saw001?->id,
                'asset_type' => 'Chainsaw',
                'maintenance_task' => 'Blade Sharpening',
                'interval_type' => 'Quarterly',
                'interval_days' => 90,
                'last_maintenance_date' => Carbon::now()->subDays(95)->format('Y-m-d'),
            ],
            // Rescue Boat / Hull Inspection / Yearly (365 days) / last done 400 days ago → Overdue
            [
                'asset_id' => $bot001?->id,
                'asset_type' => 'Rescue Boat',
                'maintenance_task' => 'Hull Inspection',
                'interval_type' => 'Yearly',
                'interval_days' => 365,
                'last_maintenance_date' => Carbon::now()->subDays(400)->format('Y-m-d'),
            ],
            // Patrol Vehicle / Brake Fluid Flush / Yearly (365 days) / last done 366 days ago → Maintenance Due
            [
                'asset_id' => $veh001?->id,
                'asset_type' => 'Patrol Vehicle',
                'maintenance_task' => 'Brake Fluid Flush',
                'interval_type' => 'Yearly',
                'interval_days' => 365,
                'last_maintenance_date' => Carbon::now()->subDays(366)->format('Y-m-d'),
            ],
            // Patrol Vehicle / Oil Filter Replacement / Quarterly (90 days) / last done 110 days ago → Overdue
            [
                'asset_id' => $veh001?->id,
                'asset_type' => 'Patrol Vehicle',
                'maintenance_task' => 'Oil Filter Replacement',
                'interval_type' => 'Quarterly',
                'interval_days' => 90,
                'last_maintenance_date' => Carbon::now()->subDays(110)->format('Y-m-d'),
            ],
        ];

        foreach ($schedules as $schedule) {
            if ($schedule['asset_id']) {
                MaintenanceSchedule::create($schedule);
            }
        }
    }
}
