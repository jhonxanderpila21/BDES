<?php

namespace Database\Seeders;

use App\Models\OperationalRequirement;
use App\Models\OperationalRequirementRestock;
use App\Models\User;
use Illuminate\Database\Seeder;

class OperationalRequirementRestockSeeder extends Seeder
{
    /**
     * Seed realistic supply restock transactions linked to operational requirements
     * and inventory staff so the Asset Expenses ledger and restock history stay connected.
     */
    public function run(): void
    {
        $custodian = User::where('role', 'inventory_officer')->first()
            ?? User::where('role', 'administrator')->first()
            ?? User::first();

        $restockEntries = [
            'Automotive Diesel Fuel (Tanker & Penetrator)' => [
                'quantity'     => 120.00,
                'unit_cost'    => 62.50,
                'total_cost'   => 7500.00,
                'restocked_at' => now()->subDays(18)->toDateString(),
                'supplier'     => 'Petron Barangay Station',
                'notes'        => 'Bulk diesel replenishment for Fire Truck Tanker and Penetrator reserve.',
            ],
            'Aqueous Film-Forming Foam (AFFF 3%)' => [
                'quantity'     => 25.00,
                'unit_cost'    => 380.00,
                'total_cost'   => 9500.00,
                'restocked_at' => now()->subDays(25)->toDateString(),
                'supplier'     => 'Metro Fire Safety & Suppression Corp.',
                'notes'        => 'Initial AFFF 3% foam concentrate delivery.',
            ],
            'Heavy-Duty Diesel Engine Oil (15W-40)' => [
                'quantity'     => 16.00,
                'unit_cost'    => 310.00,
                'total_cost'   => 4960.00,
                'restocked_at' => now()->subDays(14)->toDateString(),
                'supplier'     => 'Caltex Lubricants Depot',
                'notes'        => 'Engine and PTO pump 15W-40 lubricant replenishment.',
            ],
            '2-Stroke Fuel Premix (50:1)' => [
                'quantity'     => 14.00,
                'unit_cost'    => 95.00,
                'total_cost'   => 1330.00,
                'restocked_at' => now()->subDays(10)->toDateString(),
                'supplier'     => 'Shell Rescue Fuel Hub',
                'notes'        => '50:1 synthetic 2-stroke fuel premix for chainsaw operations.',
            ],
            'Bar and Chain Oil' => [
                'quantity'     => 6.00,
                'unit_cost'    => 240.00,
                'total_cost'   => 1440.00,
                'restocked_at' => now()->subDays(22)->toDateString(),
                'supplier'     => 'Makita & Rescue Hardware Supply',
                'notes'        => 'Bar and chain adhesive lubricant for chainsaw maintenance.',
            ],
            'ISO VG 32 Hydraulic Jack Oil' => [
                'quantity'     => 4.00,
                'unit_cost'    => 420.00,
                'total_cost'   => 1680.00,
                'restocked_at' => now()->subDays(12)->toDateString(),
                'supplier'     => 'Industrial Hydraulics PH',
                'notes'        => 'Anti-wear ISO VG 32 hydraulic fluid for rescue jacks.',
            ],
            'Diesel Fuel' => [
                'quantity'     => 40.00,
                'unit_cost'    => 63.00,
                'total_cost'   => 2520.00,
                'restocked_at' => now()->subDays(7)->toDateString(),
                'supplier'     => 'Petron Barangay Station',
                'notes'        => 'Standby generator set diesel fuel refill.',
            ],
            'Engine Oil (15W-40)' => [
                'quantity'     => 5.00,
                'unit_cost'    => 295.00,
                'total_cost'   => 1475.00,
                'restocked_at' => now()->subDays(15)->toDateString(),
                'supplier'     => 'Caltex Lubricants Depot',
                'notes'        => 'Generator 4-stroke engine oil stock.',
            ],
            'Marine Gasoline (Unleaded)' => [
                'quantity'     => 45.00,
                'unit_cost'    => 68.00,
                'total_cost'   => 3060.00,
                'restocked_at' => now()->subDays(5)->toDateString(),
                'supplier'     => 'Seaport Marine Fuel Supply',
                'notes'        => 'Octane 95 unleaded gasoline refill for rescue boat outboard motor.',
            ],
        ];

        foreach ($restockEntries as $requirementType => $entry) {
            $requirement = OperationalRequirement::where('requirement_type', $requirementType)->first();
            if (!$requirement) {
                continue;
            }

            OperationalRequirementRestock::updateOrCreate(
                [
                    'operational_requirement_id' => $requirement->id,
                    'supplier'                   => $entry['supplier'],
                    'restocked_at'               => $entry['restocked_at'],
                ],
                [
                    'quantity'    => $entry['quantity'],
                    'unit_cost'   => $entry['unit_cost'],
                    'total_cost'  => $entry['total_cost'],
                    'notes'       => $entry['notes'],
                    'recorded_by' => $custodian?->id,
                ]
            );
        }
    }
}
