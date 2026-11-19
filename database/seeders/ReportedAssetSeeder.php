<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\ReportedAsset;
use App\Models\User;
use App\Services\ReportedAssetService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReportedAssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin        = User::where('role', 'administrator')->first();
        $maintOfficer = User::whereIn('role', ['inventory_officer', 'maintenance_officer'])->first() ?? $admin;
        $staff        = User::where('role', 'viewer')->first() ?? User::where('role', 'bdrrmc_officer')->first();
        $reporter     = $staff ?? $admin;

        if (!$reporter) {
            return;
        }

        $allAssets = Asset::with('category')->get();
        if ($allAssets->isEmpty()) {
            return;
        }

        $findAsset = function (string $code) use ($allAssets) {
            return $allAssets->firstWhere('code', $code) ?? $allAssets->random();
        };

        $reports = [
            // 1. 1½-inch Fire Hose – 1 (FH-005) — Damage Report (Confirmed & Ongoing)
            [
                'asset'         => $findAsset('FH-005'),
                'reported_type' => 'Damage',
                'target_status' => 'Ongoing',
                'confirmed'     => true,
                'location'      => 'Purok A Fire Station',
                'description'   => '1½-inch Fire Hose – 1 returned from Purok B fire suppression with punctured inner rubber lining and abraded outer canvas jacket. Failed post-operation pressure test.',
                'action'        => 'Assigned to BFP Hose Maintenance Unit under Work Order WO-2026-001',
                'reported_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'transitions'   => [
                    ['to' => 'For Review', 'notes' => 'Post-deployment damage report and failed inspection findings submitted for review.'],
                    ['to' => 'Ongoing', 'notes' => 'Report confirmed by Administrator. Asset marked Poor & Out of Service; repair work order WO-2026-001 dispatched.'],
                ],
            ],

            // 2. 2½-inch Fire Hose – 1 (FH-006) — Damage Report (For Review)
            [
                'asset'         => $findAsset('FH-006'),
                'reported_type' => 'Damage',
                'target_status' => 'For Review',
                'confirmed'     => false,
                'location'      => 'Purok A Fire Station',
                'description'   => '2½-inch Fire Hose – 1 returned from Zone 2 hydrant relay with split outer jacket seam near female brass coupling.',
                'action'        => 'Pending confirmation for vulcanizing repair or coupling re-attachment (WO-2026-002)',
                'reported_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'transitions'   => [
                    ['to' => 'For Review', 'notes' => 'Return damage noted and hydrostatic test failure logged for admin confirmation.'],
                ],
            ],

            // 3. Chainsaws (2 large, 2 small) — Ongoing
            [
                'asset'         => $findAsset('SAW-001'),
                'reported_type' => 'Maintenance',
                'target_status' => 'Ongoing',
                'confirmed'     => true,
                'location'      => 'Main Barangay Hall Storage',
                'description'   => '1 of the 2 small chainsaws requires chain tensioner adjustment and guide bar dressing after heavy storm clearing.',
                'action'        => 'Adjust tensioner screw and sharpen chains on all 4 units',
                'reported_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
                'transitions'   => [
                    ['to' => 'For Review', 'notes' => 'Logged by clearing team leader after road clearing operation.'],
                    ['to' => 'Ongoing', 'notes' => 'Confirmed. In-house mechanic servicing guide bar and tensioner assembly.'],
                ],
            ],

            // 4. 2½-inch Hose Peed (Tanker & Penetrator) — Done
            [
                'asset'         => $findAsset('HP-001'),
                'reported_type' => 'Maintenance',
                'target_status' => 'Done',
                'confirmed'     => true,
                'location'      => 'Purok A Fire Station',
                'description'   => 'Quarter-turn ball valve on Tanker 2½-inch hose peed stiff to operate under pressure.',
                'action'        => 'Lubricated valve seals and replaced rubber washer',
                'reported_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'transitions'   => [
                    ['to' => 'Ongoing', 'notes' => 'Disassembled valve gate and flushed sediment.'],
                    ['to' => 'Done', 'notes' => 'Silicone grease applied and pressure-tested leak-free on Tanker.'],
                ],
            ],

            // 5. Hydraulic Jacks (3 units) — Pending
            [
                'asset'         => $findAsset('JCK-001'),
                'reported_type' => 'Maintenance',
                'target_status' => 'Pending',
                'confirmed'     => false,
                'location'      => 'Purok A Fire Station',
                'description'   => 'One of the 3 hydraulic jacks exhibits slow ram return and requires hydraulic oil bleed and seal check.',
                'action'        => 'Bleed air from hydraulic cylinder and top up jack oil',
                'reported_date' => Carbon::now()->format('Y-m-d'),
                'transitions'   => [],
            ],

            // 6. Fire Truck – Tanker — Done
            [
                'asset'         => $findAsset('TRK-001'),
                'reported_type' => 'Maintenance',
                'target_status' => 'Done',
                'confirmed'     => true,
                'location'      => 'Purok A Fire Station',
                'description'   => 'Scheduled preventive check of PTO fire pump, 7x 1½-inch hoses, 3x 2½-inch hoses, and onboard tools (spanner, yabie tubo, vice grip, cutter pliers, electrical pliers).',
                'action'        => 'Completed full apparatus & hose inventory verification',
                'reported_date' => Carbon::now()->subDays(14)->format('Y-m-d'),
                'transitions'   => [
                    ['to' => 'Ongoing', 'notes' => 'Pump pressure test and hose coupling gasket check initiated.'],
                    ['to' => 'Done', 'notes' => 'All Tanker hoses and tools verified complete and operational.'],
                ],
            ],

            // 7. Bull Cutter (1 unit) — Done
            [
                'asset'         => $findAsset('BLC-001'),
                'reported_type' => 'Maintenance',
                'target_status' => 'Done',
                'confirmed'     => true,
                'location'      => 'Purok A Fire Station',
                'description'   => 'Bull cutter jaw alignment slightly offset after cutting hardened steel padlock during fire forcible entry.',
                'action'        => 'Adjusted eccentric pivot bolts and lubricated jaws',
                'reported_date' => Carbon::now()->subDays(18)->format('Y-m-d'),
                'transitions'   => [
                    ['to' => 'Ongoing', 'notes' => 'Calibrating jaw gap tolerance.'],
                    ['to' => 'Done', 'notes' => 'Jaws aligned and tested cleanly on 10mm steel rod.'],
                ],
            ],

            // 8. Rick (14 units) & Machetes (Sundang - 4 units) — Pending
            [
                'asset'         => $findAsset('RCK-001'),
                'reported_type' => 'Maintenance',
                'target_status' => 'Pending',
                'confirmed'     => false,
                'location'      => 'Evacuation Center Warehouse',
                'description'   => 'Routine post-deployment cleaning and rust-preventive oiling required for the 14 Ricks and 4 Machetes (Sundang).',
                'action'        => 'Wash mud off tines, sharpen blades, and apply anti-rust oil',
                'reported_date' => Carbon::now()->format('Y-m-d'),
                'transitions'   => [],
            ],
        ];

        foreach ($reports as $data) {
            if (!$data['asset'] || !$reporter) {
                continue;
            }

            $data['asset']->load('category');

            $isConfirmed = !empty($data['confirmed']);

            $report = ReportedAsset::create([
                'asset_id'       => $data['asset']->id,
                'asset_category' => $data['asset']->category?->name ?? 'Uncategorized',
                'asset_name'     => $data['asset']->name,
                'reported_by'    => $reporter->id,
                'reported_type'  => $data['reported_type'],
                'status'         => ReportedAssetService::STATUS_PENDING,
                'confirmed_at'   => $isConfirmed ? Carbon::parse($data['reported_date'])->addHours(2) : null,
                'confirmed_by'   => $isConfirmed ? ($admin?->id ?? $reporter->id) : null,
                'location'       => $data['location'],
                'description'    => $data['description'],
                'action'         => $data['action'],
                'reported_date'  => $data['reported_date'],
            ]);

            if ($isConfirmed && $maintOfficer) {
                $report->assignedPersonnel()->syncWithoutDetaching([$maintOfficer->id]);
            }

            $report->statusHistories()->create([
                'from_status' => null,
                'to_status'   => ReportedAssetService::STATUS_PENDING,
                'changed_by'  => $reporter->id,
                'notes'       => 'Report created.',
            ]);

            foreach ($data['transitions'] as $transition) {
                $report->changeStatus(
                    $transition['to'],
                    $admin?->id ?? $reporter->id,
                    $transition['notes']
                );
            }
        }
    }
}
