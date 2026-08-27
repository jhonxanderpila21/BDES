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
        $admin = User::where('role', 'administrator')->first();
        $staff = User::where('role', 'viewer')->first();
        $reporter = $staff ?? $admin;

        $gen001 = Asset::where('code', 'GEN-001')->first();
        $med001 = Asset::where('code', 'MED-001')->first();
        $com001 = Asset::where('code', 'COM-001')->first();
        $res001 = Asset::where('code', 'RES-001')->first();

        $reports = [
            // 1. Generator Damage — Ongoing
            [
                'asset'         => $gen001,
                'reported_type' => 'Damage',
                'target_status' => 'Ongoing',
                'location'      => 'Evacuation Center 1',
                'description'   => 'Generator won\'t start. Possible fuel line issue.',
                'action'        => 'Repair/Refill',
                'reported_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'transitions'   => [
                    ['to' => 'For Review', 'notes' => 'Submitted for admin review.'],
                    ['to' => 'Ongoing', 'notes' => 'Approved. Technician dispatched.'],
                ],
            ],
            // 2. AED Missing — Escalated
            [
                'asset'         => $med001,
                'reported_type' => 'Missing',
                'target_status' => 'Escalated',
                'location'      => 'Barangay Hall',
                'description'   => 'AED unit not found in its assigned storage cabinet.',
                'action'        => 'Report to city office',
                'reported_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'transitions'   => [
                    ['to' => 'Ongoing', 'notes' => 'Investigation started.'],
                    ['to' => 'Escalated', 'notes' => 'Cannot locate. Escalated to city disaster office.'],
                ],
            ],
            // 3. Radio Malfunction — Pending
            [
                'asset'         => $com001,
                'reported_type' => 'Malfunction',
                'target_status' => 'Pending',
                'location'      => 'Evacuation Center 2',
                'description'   => 'Radio is producing static noise on all channels.',
                'action'        => 'Awaiting review',
                'reported_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'transitions'   => [],
            ],
            // 4. Fire Extinguisher Maintenance — Done
            [
                'asset'         => $res001,
                'reported_type' => 'Maintenance',
                'target_status' => 'Done',
                'location'      => 'Barangay Hall',
                'description'   => 'Fire extinguisher pressure gauge is in the yellow zone.',
                'action'        => 'Refilled',
                'reported_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'transitions'   => [
                    ['to' => 'Ongoing', 'notes' => 'Picked up for refilling.'],
                    ['to' => 'Done', 'notes' => 'Refilled and returned to station.'],
                ],
            ],
        ];

        foreach ($reports as $data) {
            if (!$data['asset']) {
                continue;
            }

            $report = ReportedAsset::create([
                'asset_id'      => $data['asset']->id,
                'reported_by'   => $reporter->id,
                'reported_type' => $data['reported_type'],
                'status'        => ReportedAssetService::STATUS_PENDING,
                'location'      => $data['location'],
                'description'   => $data['description'],
                'action'        => $data['action'],
                'reported_date' => $data['reported_date'],
            ]);

            // Log initial status
            $report->statusHistories()->create([
                'from_status' => null,
                'to_status'   => ReportedAssetService::STATUS_PENDING,
                'changed_by'  => $reporter->id,
                'notes'       => 'Report created.',
            ]);

            // Walk through transitions
            foreach ($data['transitions'] as $transition) {
                $report->changeStatus(
                    $transition['to'],
                    $admin->id ?? $reporter->id,
                    $transition['notes']
                );
            }
        }
    }
}
