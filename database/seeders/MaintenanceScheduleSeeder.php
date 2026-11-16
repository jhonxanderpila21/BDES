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
        $tanker     = Asset::where('code', 'TRK-001')->first();
        $penetrator = Asset::where('code', 'TRK-002')->first();
        $chainsaw   = Asset::where('code', 'SAW-001')->first();
        $hose15Tnk  = Asset::where('code', 'FH-001')->first();
        $jack       = Asset::where('code', 'JCK-001')->first();

        $schedules = [
            // Fire Truck – Tanker / Pump & Engine Oil Change / Monthly (30 days)
            [
                'asset_id'              => $tanker?->id,
                'asset_type'            => 'Fire Truck',
                'maintenance_task'      => 'Fire Pump Test & Engine Oil Check (Tanker)',
                'interval_type'         => 'Monthly',
                'interval_days'         => 30,
                'last_maintenance_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
            ],

            // Fire Truck – Penetrator / Brake & Hose Peed Valve Check / Quarterly (90 days)
            [
                'asset_id'              => $penetrator?->id,
                'asset_type'            => 'Fire Truck',
                'maintenance_task'      => 'Brake System & 2½-inch Hose Peed Valve Service (Penetrator)',
                'interval_type'         => 'Quarterly',
                'interval_days'         => 90,
                'last_maintenance_date' => Carbon::now()->subDays(88)->format('Y-m-d'),
            ],

            // Chainsaws (2 large, 2 small) / Chain Sharpening & Carburetor Tune-up / Quarterly (90 days) -> Maintenance Due
            [
                'asset_id'              => $chainsaw?->id,
                'asset_type'            => 'Chainsaw',
                'maintenance_task'      => 'Chain Sharpening & Carburetor Tune-Up (2 Large, 2 Small)',
                'interval_type'         => 'Quarterly',
                'interval_days'         => 90,
                'last_maintenance_date' => Carbon::now()->subDays(95)->format('Y-m-d'),
            ],

            // 1½-inch & 2½-inch Fire Hoses / Hydrostatic Pressure Testing / Yearly (365 days) -> Overdue
            [
                'asset_id'              => $hose15Tnk?->id,
                'asset_type'            => 'Fire Hose & Fittings',
                'maintenance_task'      => 'Annual Fire Hose Hydrostatic Pressure & Coupling Gasket Test',
                'interval_type'         => 'Yearly',
                'interval_days'         => 365,
                'last_maintenance_date' => Carbon::now()->subDays(400)->format('Y-m-d'),
            ],

            // Hydraulic Jacks (3 units) / Hydraulic Seal & Oil Level Inspection / Quarterly (90 days) -> Overdue
            [
                'asset_id'              => $jack?->id,
                'asset_type'            => 'Hydraulic & Lifting Tool',
                'maintenance_task'      => 'Hydraulic Cylinder Seal & Oil Bleed Check (3 Jacks)',
                'interval_type'         => 'Quarterly',
                'interval_days'         => 90,
                'last_maintenance_date' => Carbon::now()->subDays(110)->format('Y-m-d'),
            ],
        ];

        foreach ($schedules as $schedule) {
            if ($schedule['asset_id']) {
                $schedule['next_due_date'] = Carbon::parse(
                    $schedule['last_maintenance_date']
                )
                    ->addDays($schedule['interval_days'])
                    ->format('Y-m-d');

                MaintenanceSchedule::create($schedule);
            }
        }
    }
}
