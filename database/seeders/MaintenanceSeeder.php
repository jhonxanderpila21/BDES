<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin        = User::where('role', 'administrator')->first() ?? User::first();
        $maintOfficer = User::whereIn('role', ['inventory_officer', 'maintenance_officer'])->first() ?? $admin;

        $tanker     = Asset::where('code', 'TRK-001')->first();
        $penetrator = Asset::where('code', 'TRK-002')->first();
        $chainsaw   = Asset::where('code', 'SAW-001')->first();
        $hose15Dmg  = Asset::where('code', 'FH-005')->first();
        $hose25Dmg  = Asset::where('code', 'FH-006')->first();

        $maintenances = [
            // Damage Flow Work Order 1: 1½-inch Fire Hose – 1 (FH-005)
            [
                'asset_id'           => $hose15Dmg?->id,
                'type'               => 'repair',
                'priority'           => 'high',
                'work_order_number'  => 'WO-2026-001',
                'technician'         => 'BFP Apparatus & Hose Maintenance Unit',
                'assigned_to'        => $maintOfficer?->id,
                'confirmed_at'       => now()->subDays(4),
                'confirmed_by'       => $admin?->id,
                'scheduled_date'     => now()->subDays(4),
                'estimated_duration' => '2 days',
                'notes'              => '1½-inch Fire Hose – 1 (FH-005): Repairing punctured inner rubber liner and re-expanding brass coupling ring following Purok B fire deployment damage.',
                'status'             => 'in_progress',
            ],
            // Damage Flow Work Order 2: 2½-inch Fire Hose – 1 (FH-006)
            [
                'asset_id'           => $hose25Dmg?->id,
                'type'               => 'repair',
                'priority'           => 'high',
                'work_order_number'  => 'WO-2026-002',
                'technician'         => 'BFP Apparatus & Hose Maintenance Unit',
                'assigned_to'        => $maintOfficer?->id,
                'scheduled_date'     => now()->subDays(2),
                'estimated_duration' => '3 days',
                'notes'              => '2½-inch Fire Hose – 1 (FH-006): Evaluating split outer jacket seam near female coupling for vulcanizing patch and hydrostatic re-certification.',
                'status'             => 'pending',
            ],
            [
                'asset_id'           => $chainsaw?->id,
                'type'               => 'routine',
                'priority'           => 'medium',
                'work_order_number'  => 'WO-2026-003',
                'technician'         => 'Mang Kanor (In-house Mechanic)',
                'assigned_to'        => $maintOfficer?->id,
                'scheduled_date'     => now()->addDays(5),
                'estimated_duration' => '1 day',
                'notes'              => 'Chainsaws (2 large, 2 small): Guide bar dressing, chain sharpening, spark plug check, and air filter cleaning.',
                'status'             => 'scheduled',
            ],
            [
                'asset_id'           => $tanker?->id,
                'type'               => 'preventive',
                'priority'           => 'medium',
                'work_order_number'  => 'WO-2026-004',
                'technician'         => 'Municipal Motor Pool & Fire Pump Specialist',
                'assigned_to'        => $maintOfficer?->id,
                'confirmed_at'       => now()->subMonths(1),
                'confirmed_by'       => $admin?->id,
                'verified_by'        => $admin?->id,
                'final_condition'    => 'good',
                'scheduled_date'     => now()->subMonths(1),
                'completed_at'       => now()->subDays(28),
                'estimated_duration' => '2 days',
                'cost'               => 4500.00,
                'notes'              => 'Fire Truck – Tanker: PTO fire pump pressure test, valve lubrication, and engine oil change.',
                'status'             => 'completed',
            ],
            [
                'asset_id'           => $penetrator?->id,
                'type'               => 'routine',
                'priority'           => 'low',
                'work_order_number'  => 'WO-2026-005',
                'technician'         => 'Municipal Motor Pool',
                'assigned_to'        => $maintOfficer?->id,
                'confirmed_at'       => now()->subMonths(2),
                'confirmed_by'       => $admin?->id,
                'verified_by'        => $admin?->id,
                'final_condition'    => 'good',
                'scheduled_date'     => now()->subMonths(2),
                'completed_at'       => now()->subDays(58),
                'estimated_duration' => '1 day',
                'cost'               => 2800.00,
                'notes'              => 'Fire Truck – Penetrator: Brake system check, 2½-inch hose peed valve seal inspection, and fluid top-up.',
                'status'             => 'completed',
            ],
        ];

        foreach ($maintenances as $maintenance) {
            if (!empty($maintenance['asset_id'])) {
                $record = Maintenance::updateOrCreate(
                    ['work_order_number' => $maintenance['work_order_number']],
                    $maintenance
                );
                if ($maintOfficer && !empty($maintenance['confirmed_at'])) {
                    $record->assignedPersonnel()->syncWithoutDetaching([$maintOfficer->id]);
                }
            }
        }
    }
}
